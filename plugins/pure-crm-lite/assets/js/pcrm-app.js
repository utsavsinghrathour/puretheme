(() => {
  const cfg = window.PCRM_APP || {};
  const restUrl = cfg.restUrl || '';
  const leadUrl = cfg.leadUrl || '';
  const nonce = cfg.nonce || '';
  const i18n = cfg.strings || {};

  const state = {
    metrics: {},
    contacts: [],
    deals: [],
    tasks: [],
    funnels: [],
    default_funnel: null,
    smtp_accounts: [],
    email_logs: [],
    notifications: { count: 0, items: [] },
    help: [],
    activeFunnelId: 0,
    editingTaskId: 0,
  };

  const qs = (s, root = document) => root.querySelector(s);
  const qsa = (s, root = document) => Array.from(root.querySelectorAll(s));
  const safe = (v) => String(v ?? '').replace(/[&<>"']/g, (m) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[m]));
  const emptyText = i18n.empty || 'No records yet.';
  const loadingText = i18n.loading || 'Loading...';

  function toast(message, type = 'ok') {
    const anchor = qs('#pcrm-toast-anchor');
    if (!anchor) return;
    const el = document.createElement('div');
    el.className = `pcrm-toast ${type === 'bad' ? 'is-error' : 'is-success'}`;
    el.textContent = message;
    anchor.appendChild(el);
    window.setTimeout(() => el.remove(), 3200);
  }

  function headers() {
    return {
      'Content-Type': 'application/json',
      'X-WP-Nonce': nonce,
    };
  }

  async function api(endpoint, method = 'GET', data = null) {
    const res = await fetch(restUrl + endpoint, {
      method,
      credentials: 'same-origin',
      headers: headers(),
      body: data ? JSON.stringify(data) : undefined,
    });
    let payload = null;
    try {
      payload = await res.json();
    } catch (e) {
      payload = null;
    }
    if (!res.ok) {
      throw new Error(payload?.message || `Request failed (${res.status})`);
    }
    return payload;
  }

  function formData(form) {
    const fd = new FormData(form);
    const out = {};
    fd.forEach((val, key) => {
      out[key] = typeof val === 'string' ? val.trim() : val;
    });
    qsa('input[type="checkbox"]', form).forEach((c) => {
      if (!fd.has(c.name)) out[c.name] = '';
    });
    return out;
  }

  function metricCards() {
    const root = qs('#pcrm-metrics');
    if (!root) return;
    const m = state.metrics || {};
    const cards = [
      ['Contacts', m.contacts_count || 0],
      ['Open Deals', m.open_deals_count || 0],
      ['Won Deals', m.won_deals_count || 0],
      ['Pipeline', money(m.pipeline_value || 0)],
      ['Overdue Tasks', m.overdue_tasks_count || 0],
      ['Follow-ups Due', m.followups_due_count || 0],
      ['Funnels', m.funnels_count || 0],
    ];
    root.innerHTML = cards.map(([label, val]) => `
      <article class="pcrm-metric-card">
        <span>${safe(label)}</span>
        <strong>${safe(val)}</strong>
      </article>
    `).join('');
  }

  function money(v) {
    return new Intl.NumberFormat(undefined, { style: 'currency', currency: 'USD' }).format(Number(v || 0));
  }

  function renderHelp() {
    const root = qs('#pcrm-help-list');
    if (!root) return;
    const list = state.help || [];
    if (!list.length) {
      root.innerHTML = `<p class="pcrm-muted">${safe(emptyText)}</p>`;
      return;
    }
    root.innerHTML = list.map((h) => `
      <article class="pcrm-help-item">
        <h4>${safe(h.title || '')}</h4>
        <p>${safe(h.content || '')}</p>
      </article>
    `).join('');
  }

  function renderNotifications() {
    const root = qs('#pcrm-notifications');
    if (!root) return;
    const payload = state.notifications || { items: [] };
    if (!payload.items?.length) {
      root.innerHTML = `<p class="pcrm-muted">No urgent follow-ups right now.</p>`;
      return;
    }
    root.innerHTML = payload.items.map((n) => `
      <article class="pcrm-note ${n.priority === 'high' ? 'is-high' : ''}">
        <h4>${safe(n.title || '')}</h4>
        <p>${safe(n.message || '')}</p>
        <small>${safe(formatDt(n.due_at))}</small>
      </article>
    `).join('');
  }

  function renderContacts() {
    const root = qs('#pcrm-contacts-table');
    if (!root) return;
    if (!state.contacts.length) {
      root.innerHTML = `<p class="pcrm-muted">${safe(emptyText)}</p>`;
      return;
    }
    root.innerHTML = `
      <table class="pcrm-table">
        <thead><tr><th>Name</th><th>Email</th><th>Company</th><th>Phone</th><th>Source</th><th></th></tr></thead>
        <tbody>
          ${state.contacts.map((c) => `
            <tr>
              <td>${safe(name(c) || '(No name)')}</td>
              <td>${safe(c.email || '')}</td>
              <td>${safe(c.company || '')}</td>
              <td>${safe(c.phone || '')}</td>
              <td>${safe(c.source || '')}</td>
              <td><button class="pcrm-btn-link" data-delete-contact="${c.id}">Delete</button></td>
            </tr>
          `).join('')}
        </tbody>
      </table>
    `;
  }

  function renderFunnels() {
    const listRoot = qs('#pcrm-funnel-list');
    const dealFunnel = qs('#pcrm-deal-funnel');
    const activeFunnel = qs('#pcrm-active-funnel');
    if (dealFunnel) {
      dealFunnel.innerHTML = state.funnels.map((f) => `<option value="${f.id}">${safe(f.name)}${f.is_default ? ' (Default)' : ''}</option>`).join('');
    }
    if (activeFunnel) {
      activeFunnel.innerHTML = state.funnels.map((f) => `<option value="${f.id}" ${Number(state.activeFunnelId) === Number(f.id) ? 'selected' : ''}>${safe(f.name)}</option>`).join('');
    }
    if (!listRoot) return;
    if (!state.funnels.length) {
      listRoot.innerHTML = `<p class="pcrm-muted">${safe(emptyText)}</p>`;
      return;
    }
    listRoot.innerHTML = state.funnels.map((f) => `
      <article class="pcrm-funnel-row">
        <div>
          <h4>${safe(f.name)} ${f.is_default ? '<span class="pcrm-chip">Default</span>' : ''}</h4>
          <p>${(f.stages || []).map((s) => safe(s.label)).join(' -> ')}</p>
        </div>
        <div class="pcrm-inline-actions">
          ${f.is_default ? '' : `<button class="pcrm-btn-link" data-funnel-default="${f.id}">Set Default</button>`}
          <button class="pcrm-btn-link" data-funnel-delete="${f.id}">Delete</button>
        </div>
      </article>
    `).join('');
  }

  function stageMapForFunnel(fid) {
    const f = state.funnels.find((x) => Number(x.id) === Number(fid));
    return f?.stage_map || {};
  }

  function renderDealsKanban() {
    const root = qs('#pcrm-funnels-board');
    if (!root) return;
    const funnelId = Number(state.activeFunnelId || state.default_funnel?.id || 0);
    const map = stageMapForFunnel(funnelId);
    const stages = Object.keys(map);
    if (!stages.length) {
      root.innerHTML = `<p class="pcrm-muted">Create a funnel with stages first.</p>`;
      return;
    }
    const deals = state.deals.filter((d) => Number(d.funnel_id) === funnelId);
    root.innerHTML = stages.map((stageKey) => {
      const laneDeals = deals.filter((d) => d.stage === stageKey);
      return `
        <section class="pcrm-lane" data-stage="${safe(stageKey)}">
          <header><h4>${safe(map[stageKey])}</h4><span>${laneDeals.length}</span></header>
          <div class="pcrm-lane-drop" data-drop-stage="${safe(stageKey)}">
            ${laneDeals.map((d) => `
              <article class="pcrm-deal-card" draggable="true" data-deal-id="${d.id}" data-deal-stage="${safe(d.stage)}">
                <h5>${safe(d.title)}</h5>
                <p>${safe(d.contact_name || d.contact_email || 'No contact')}</p>
                <strong>${safe(money(d.value || 0))}</strong>
                <small>Follow-up: ${safe(formatDt(d.next_follow_up))}</small>
                <div class="pcrm-inline-actions">
                  <button class="pcrm-btn-link" data-delete-deal="${d.id}">Delete</button>
                </div>
              </article>
            `).join('')}
          </div>
        </section>
      `;
    }).join('');
    bindKanbanDnD();
  }

  function bindKanbanDnD() {
    qsa('.pcrm-deal-card[draggable="true"]').forEach((card) => {
      card.addEventListener('dragstart', (e) => {
        e.dataTransfer?.setData('text/plain', card.getAttribute('data-deal-id') || '');
      });
    });
    qsa('[data-drop-stage]').forEach((lane) => {
      lane.addEventListener('dragover', (e) => e.preventDefault());
      lane.addEventListener('drop', async (e) => {
        e.preventDefault();
        const dealId = e.dataTransfer?.getData('text/plain');
        const stage = lane.getAttribute('data-drop-stage');
        if (!dealId || !stage) return;
        try {
          await api(`deals/${dealId}`, 'PUT', { stage });
          await refreshAll();
          toast(i18n.dropHint || 'Deal stage updated.');
        } catch (err) {
          toast(err.message, 'bad');
        }
      });
    });
  }

  function renderTasks() {
    const root = qs('#pcrm-tasks-list');
    const dash = qs('#pcrm-dashboard-tasks');
    if (!root && !dash) return;
    if (!state.tasks.length) {
      if (root) root.innerHTML = `<p class="pcrm-muted">${safe(emptyText)}</p>`;
      if (dash) dash.innerHTML = `<p class="pcrm-muted">${safe(emptyText)}</p>`;
      return;
    }
    const html = state.tasks.map((t) => `
      <article class="pcrm-task-item">
        <div>
          <h5>${safe(t.title)}</h5>
          <p>${safe(t.description || '')}</p>
          <small>${safe(t.status)} • ${safe(t.priority)} • ${safe(t.contact_name || t.contact_email || 'Unassigned')} ${t.due_date ? `• due ${safe(formatDt(t.due_date))}` : ''}</small>
        </div>
        <div class="pcrm-inline-actions">
          <button class="pcrm-btn-link" data-edit-task="${t.id}">Edit</button>
          <button class="pcrm-btn-link" data-delete-task="${t.id}">Delete</button>
        </div>
      </article>
    `).join('');
    if (root) root.innerHTML = html;
    if (dash) dash.innerHTML = state.tasks.slice(0, 6).map((t) => `<div class="pcrm-mini-row"><span>${safe(t.title)}</span><strong>${safe(t.status)}</strong></div>`).join('');
  }

  function renderEmails() {
    const root = qs('#pcrm-email-logs');
    const dash = qs('#pcrm-dashboard-emails');
    const select = qs('#pcrm-email-smtp');
    if (select) {
      select.innerHTML = ['<option value="">Use default sender</option>']
        .concat(state.smtp_accounts.map((a) => `<option value="${a.id}">${safe(a.label)} (${safe(a.from_email)})${a.is_default ? ' - Default' : ''}</option>`))
        .join('');
    }
    if (root) {
      const list = state.email_logs || [];
      if (!list.length) {
        root.innerHTML = `<p class="pcrm-muted">${safe(emptyText)}</p>`;
      } else {
        root.innerHTML = `
          <table class="pcrm-table">
            <thead><tr><th>When</th><th>From</th><th>To</th><th>Subject</th><th>Status</th></tr></thead>
            <tbody>
              ${list.map((l) => {
                const rec = parseRecipients(l.recipients);
                return `<tr><td>${safe(formatDt(l.created_at))}</td><td>${safe(l.smtp_label || l.from_email || 'N/A')}</td><td>${safe(rec)}</td><td>${safe(l.subject || '')}</td><td>${safe(l.status || '')}</td></tr>`;
              }).join('')}
            </tbody>
          </table>
        `;
      }
    }
    if (dash) {
      dash.innerHTML = (state.email_logs || []).slice(0, 6).map((l) => `<div class="pcrm-mini-row"><span>${safe(l.subject || '(No subject)')}</span><strong>${safe(l.status || '')}</strong></div>`).join('');
    }
  }

  function parseRecipients(raw) {
    try {
      const p = JSON.parse(raw || '[]');
      return Array.isArray(p) ? p.join(', ') : '';
    } catch (e) {
      return raw || '';
    }
  }

  function renderSmtp() {
    const root = qs('#pcrm-smtp-list');
    if (!root) return;
    if (!state.smtp_accounts.length) {
      root.innerHTML = `<p class="pcrm-muted">No SMTP accounts yet.</p>`;
      return;
    }
    root.innerHTML = state.smtp_accounts.map((a) => `
      <article class="pcrm-smtp-card ${a.active ? '' : 'is-inactive'}">
        <div>
          <h5>${safe(a.label)} ${a.is_default ? '<span class="pcrm-chip">Default</span>' : ''}</h5>
          <p>${safe(a.from_name || a.from_email)} &lt;${safe(a.from_email)}&gt;</p>
          <small>${safe(a.host)}:${safe(a.port)} • ${safe((a.encryption || '').toUpperCase())}</small>
        </div>
        <div class="pcrm-inline-actions">
          ${a.is_default ? '' : `<button class="pcrm-btn-link" data-make-default-smtp="${a.id}">Set Default</button>`}
          <button class="pcrm-btn-link" data-delete-smtp="${a.id}">Delete</button>
        </div>
      </article>
    `).join('');
  }

  function fillHelpers() {
    const contactOptions = ['<option value="">Select contact</option>'].concat(
      state.contacts.map((c) => `<option value="${c.id}">${safe(name(c) || c.email)} (${safe(c.email || '')})</option>`)
    ).join('');
    const dealContact = qs('#pcrm-deal-contact');
    const taskContact = qs('#pcrm-task-contact');
    if (dealContact) dealContact.innerHTML = contactOptions;
    if (taskContact) taskContact.innerHTML = ['<option value="">Unassigned</option>'].concat(
      state.contacts.map((c) => `<option value="${c.id}">${safe(name(c) || c.email)}</option>`)
    ).join('');

    const activeFunnel = state.activeFunnelId || state.default_funnel?.id || state.funnels?.[0]?.id || 0;
    state.activeFunnelId = Number(activeFunnel || 0);
    const stageMap = stageMapForFunnel(state.activeFunnelId);
    const dealStage = qs('#pcrm-deal-stage');
    if (dealStage) {
      dealStage.innerHTML = Object.entries(stageMap).map(([k, v]) => `<option value="${safe(k)}">${safe(v)}</option>`).join('');
    }
  }

  function renderAll() {
    metricCards();
    renderHelp();
    renderNotifications();
    renderContacts();
    renderFunnels();
    fillHelpers();
    renderDealsKanban();
    renderTasks();
    renderEmails();
    renderSmtp();
  }

  async function refreshAll() {
    setLoading();
    const b = await api('bootstrap');
    state.metrics = b.metrics || {};
    state.contacts = b.contacts || [];
    state.deals = b.deals || [];
    state.tasks = b.tasks || [];
    state.funnels = b.funnels || [];
    state.default_funnel = b.default_funnel || null;
    state.smtp_accounts = b.smtp_accounts || [];
    state.email_logs = b.email_logs || [];
    state.notifications = b.notifications || { count: 0, items: [] };
    state.help = b.help || [];
    if (!state.activeFunnelId) {
      state.activeFunnelId = Number(state.default_funnel?.id || state.funnels?.[0]?.id || 0);
    }
    renderAll();
  }

  function setLoading() {
    ['#pcrm-metrics', '#pcrm-help-list', '#pcrm-notifications', '#pcrm-contacts-table', '#pcrm-funnels-board', '#pcrm-tasks-list', '#pcrm-email-logs', '#pcrm-smtp-list']
      .forEach((s) => {
        const el = qs(s);
        if (el) el.innerHTML = `<p class="pcrm-muted">${safe(loadingText)}</p>`;
      });
  }

  function bindNav() {
    const app = qs('#pcrm-app');
    if (!app) return;
    qsa('.pcrm-nav-btn', app).forEach((btn) => {
      btn.addEventListener('click', () => {
        const sec = btn.getAttribute('data-section');
        qsa('.pcrm-nav-btn', app).forEach((n) => n.classList.remove('is-active'));
        qsa('.pcrm-section', app).forEach((s) => s.classList.remove('is-active'));
        btn.classList.add('is-active');
        qs(`.pcrm-section[data-section="${sec}"]`, app)?.classList.add('is-active');
      });
    });
  }

  function bindForms() {
    const contact = qs('#pcrm-contact-form');
    if (contact) {
      contact.addEventListener('submit', async (e) => {
        e.preventDefault();
        try {
          await api('contacts', 'POST', formData(contact));
          contact.reset();
          await refreshAll();
          toast(i18n.saved || 'Saved.');
        } catch (err) {
          toast(err.message, 'bad');
        }
      });
    }

    const funnel = qs('#pcrm-funnel-form');
    if (funnel) {
      funnel.addEventListener('submit', async (e) => {
        e.preventDefault();
        const payload = formData(funnel);
        payload.is_default = payload.is_default ? 1 : 0;
        try {
          await api('funnels', 'POST', payload);
          funnel.reset();
          await refreshAll();
          toast(i18n.saved || 'Saved.');
        } catch (err) {
          toast(err.message, 'bad');
        }
      });
    }

    const deal = qs('#pcrm-deal-form');
    if (deal) {
      deal.addEventListener('submit', async (e) => {
        e.preventDefault();
        const payload = formData(deal);
        if (payload.next_follow_up) payload.next_follow_up = payload.next_follow_up.replace('T', ' ');
        if (!payload.contact_id) {
          toast('Choose a contact for this deal.', 'bad');
          return;
        }
        try {
          await api('deals', 'POST', payload);
          deal.reset();
          await refreshAll();
          toast(i18n.saved || 'Saved.');
        } catch (err) {
          toast(err.message, 'bad');
        }
      });
    }

    const task = qs('#pcrm-task-form');
    const cancelEdit = qs('#pcrm-task-cancel-edit');
    if (cancelEdit) {
      cancelEdit.addEventListener('click', () => clearTaskForm());
    }
    if (task) {
      task.addEventListener('submit', async (e) => {
        e.preventDefault();
        const payload = formData(task);
        const id = Number(payload.id || 0);
        delete payload.id;
        if (payload.due_date) payload.due_date = payload.due_date.replace('T', ' ');
        try {
          if (id > 0) {
            await api(`tasks/${id}`, 'PUT', payload);
          } else {
            await api('tasks', 'POST', payload);
          }
          clearTaskForm();
          await refreshAll();
          toast(i18n.saved || 'Saved.');
        } catch (err) {
          toast(err.message, 'bad');
        }
      });
    }

    const smtp = qs('#pcrm-smtp-form');
    if (smtp) {
      smtp.addEventListener('submit', async (e) => {
        e.preventDefault();
        const payload = formData(smtp);
        payload.is_default = payload.is_default ? 1 : 0;
        payload.active = payload.active ? 1 : 0;
        try {
          await api('smtp', 'POST', payload);
          smtp.reset();
          const port = qs('input[name="port"]', smtp);
          if (port) port.value = '587';
          const active = qs('input[name="active"]', smtp);
          if (active) active.checked = true;
          await refreshAll();
          toast(i18n.saved || 'Saved.');
        } catch (err) {
          toast(err.message, 'bad');
        }
      });
    }

    const email = qs('#pcrm-email-form');
    if (email) {
      email.addEventListener('submit', async (e) => {
        e.preventDefault();
        const payload = formData(email);
        if (!payload.smtp_account_id) delete payload.smtp_account_id;
        try {
          await api('emails/send', 'POST', payload);
          email.reset();
          await refreshAll();
          toast(i18n.sendOk || 'Email sent.');
        } catch (err) {
          toast(err.message, 'bad');
        }
      });
    }

    qsa('.pcrm-lead-form').forEach((form) => {
      form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const msg = qs('.pcrm-lead-msg', form);
        const payload = formData(form);
        payload.source = form.getAttribute('data-source') || payload.source || 'Website Lead Form';
        try {
          const res = await fetch(leadUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload),
          });
          if (!res.ok) throw new Error('Lead submit failed');
          form.reset();
          if (msg) msg.textContent = i18n.leadSuccess || 'Thanks!';
        } catch (err) {
          if (msg) msg.textContent = i18n.leadError || 'Please try again.';
        }
      });
    });
  }

  function bindDynamic() {
    document.addEventListener('click', async (e) => {
      const delContact = e.target?.getAttribute?.('data-delete-contact');
      if (delContact) {
        try {
          await api(`contacts/${delContact}`, 'DELETE');
          await refreshAll();
          toast(i18n.deleted || 'Deleted.');
        } catch (err) {
          toast(err.message, 'bad');
        }
      }

      const delDeal = e.target?.getAttribute?.('data-delete-deal');
      if (delDeal) {
        try {
          await api(`deals/${delDeal}`, 'DELETE');
          await refreshAll();
          toast(i18n.deleted || 'Deleted.');
        } catch (err) {
          toast(err.message, 'bad');
        }
      }

      const delTask = e.target?.getAttribute?.('data-delete-task');
      if (delTask) {
        try {
          await api(`tasks/${delTask}`, 'DELETE');
          await refreshAll();
          toast(i18n.deleted || 'Deleted.');
        } catch (err) {
          toast(err.message, 'bad');
        }
      }

      const editTask = e.target?.getAttribute?.('data-edit-task');
      if (editTask) {
        const task = state.tasks.find((t) => Number(t.id) === Number(editTask));
        if (task) fillTaskForm(task);
      }

      const funnelDefault = e.target?.getAttribute?.('data-funnel-default');
      if (funnelDefault) {
        try {
          await api(`funnels/${funnelDefault}/default`, 'POST', {});
          await refreshAll();
          toast(i18n.saved || 'Saved.');
        } catch (err) {
          toast(err.message, 'bad');
        }
      }

      const funnelDelete = e.target?.getAttribute?.('data-funnel-delete');
      if (funnelDelete) {
        try {
          await api(`funnels/${funnelDelete}`, 'DELETE');
          await refreshAll();
          toast(i18n.deleted || 'Deleted.');
        } catch (err) {
          toast(err.message, 'bad');
        }
      }

      const smtpDefault = e.target?.getAttribute?.('data-make-default-smtp');
      if (smtpDefault) {
        try {
          await api(`smtp/${smtpDefault}/default`, 'POST', {});
          await refreshAll();
          toast(i18n.saved || 'Saved.');
        } catch (err) {
          toast(err.message, 'bad');
        }
      }

      const smtpDelete = e.target?.getAttribute?.('data-delete-smtp');
      if (smtpDelete) {
        try {
          await api(`smtp/${smtpDelete}`, 'DELETE');
          await refreshAll();
          toast(i18n.deleted || 'Deleted.');
        } catch (err) {
          toast(err.message, 'bad');
        }
      }
    });

    const activeFunnel = qs('#pcrm-active-funnel');
    if (activeFunnel) {
      activeFunnel.addEventListener('change', () => {
        state.activeFunnelId = Number(activeFunnel.value || 0);
        fillHelpers();
        renderDealsKanban();
      });
    }

    const contactSearch = qs('#pcrm-contact-search');
    if (contactSearch) {
      let timer = null;
      contactSearch.addEventListener('input', () => {
        window.clearTimeout(timer);
        timer = window.setTimeout(async () => {
          try {
            state.contacts = await api(`contacts?search=${encodeURIComponent(contactSearch.value)}`);
            renderContacts();
            fillHelpers();
          } catch (err) {
            toast(err.message, 'bad');
          }
        }, 280);
      });
    }
  }

  function fillTaskForm(task) {
    const form = qs('#pcrm-task-form');
    if (!form || !task) return;
    state.editingTaskId = Number(task.id || 0);
    qs('#pcrm-task-id', form).value = String(task.id || '');
    const setVal = (n, v) => { const el = qs(`[name="${n}"]`, form); if (el) el.value = v ?? ''; };
    setVal('title', task.title || '');
    setVal('description', task.description || '');
    setVal('contact_id', task.contact_id || '');
    setVal('related_type', task.related_type || 'contact');
    setVal('related_id', task.related_id || '');
    setVal('status', task.status || 'open');
    setVal('priority', task.priority || 'normal');
    setVal('due_date', toLocal(task.due_date));
  }

  function clearTaskForm() {
    const form = qs('#pcrm-task-form');
    if (!form) return;
    form.reset();
    const hidden = qs('#pcrm-task-id', form);
    if (hidden) hidden.value = '';
    state.editingTaskId = 0;
  }

  function toLocal(datetime) {
    if (!datetime) return '';
    const dt = new Date(datetime.replace(' ', 'T') + 'Z');
    if (Number.isNaN(dt.getTime())) return '';
    const pad = (n) => String(n).padStart(2, '0');
    return `${dt.getFullYear()}-${pad(dt.getMonth() + 1)}-${pad(dt.getDate())}T${pad(dt.getHours())}:${pad(dt.getMinutes())}`;
  }

  function formatDt(datetime) {
    if (!datetime) return 'Not set';
    const d = new Date(datetime.replace(' ', 'T') + 'Z');
    if (Number.isNaN(d.getTime())) return datetime;
    return d.toLocaleString();
  }

  function name(c) {
    return `${c.first_name || ''} ${c.last_name || ''}`.trim();
  }

  async function init() {
    if (!qs('#pcrm-app') && !qsa('.pcrm-lead-form').length) return;
    bindForms();
    bindDynamic();
    if (qs('#pcrm-app')) {
      bindNav();
      try {
        await refreshAll();
      } catch (err) {
        toast(err.message, 'bad');
      }
    }
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
