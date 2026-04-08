(() => {
  const appConfig = window.PCRM_APP || {};
  const restUrl = appConfig.restUrl || '';
  const leadUrl = appConfig.leadUrl || '';
  const nonce = appConfig.nonce || '';
  const i18n = appConfig.strings || {};

  const state = {
    metrics: {},
    contacts: [],
    deals: [],
    tasks: [],
    smtp_accounts: [],
    email_logs: [],
    deal_stages: {},
  };

  const qs = (selector, root = document) => root.querySelector(selector);
  const qsa = (selector, root = document) => Array.from(root.querySelectorAll(selector));
  const safe = (value) => String(value ?? '').replace(/[&<>"']/g, (s) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[s]));
  const loadingText = i18n.loading || 'Loading...';
  const emptyText = i18n.empty || 'No records yet.';

  function toast(message, type = 'ok') {
    const anchor = qs('#pcrm-toast-anchor');
    if (!anchor) return;
    const node = document.createElement('div');
    node.className = `pcrm-toast pcrm-toast-${type}`;
    node.textContent = message;
    anchor.appendChild(node);
    window.setTimeout(() => {
      node.classList.add('fade-out');
      window.setTimeout(() => node.remove(), 280);
    }, 3200);
  }

  function getHeaders() {
    return {
      'Content-Type': 'application/json',
      'X-WP-Nonce': nonce,
    };
  }

  async function api(endpoint, method = 'GET', data = null) {
    const res = await fetch(restUrl + endpoint, {
      method,
      headers: getHeaders(),
      credentials: 'same-origin',
      body: data ? JSON.stringify(data) : undefined,
    });
    let payload = null;
    try {
      payload = await res.json();
    } catch (e) {
      payload = null;
    }
    if (!res.ok) {
      const message = payload?.message || payload?.code || `Request failed: ${res.status}`;
      throw new Error(message);
    }
    return payload;
  }

  function setLoadingPlaceholders() {
    const sections = [
      '#pcrm-metrics',
      '#pcrm-contacts-table',
      '#pcrm-funnels-board',
      '#pcrm-tasks-list',
      '#pcrm-smtp-list',
      '#pcrm-email-logs',
      '#pcrm-dashboard-tasks',
      '#pcrm-dashboard-emails',
    ];
    sections.forEach((selector) => {
      const el = qs(selector);
      if (el) el.innerHTML = `<p class="pcrm-muted">${safe(loadingText)}</p>`;
    });
  }

  function renderMetrics() {
    const metricRoot = qs('#pcrm-metrics');
    if (!metricRoot) return;
    const m = state.metrics || {};
    const cards = [
      ['Contacts', m.contacts_count || 0],
      ['Open Deals', m.open_deals_count || 0],
      ['Won Deals', m.won_deals_count || 0],
      ['Pipeline Value', formatCurrency(m.pipeline_value || 0)],
      ['Overdue Tasks', m.overdue_tasks_count || 0],
    ];
    metricRoot.innerHTML = cards.map(([label, value]) => `
      <article class="pcrm-metric-card">
        <span>${safe(label)}</span>
        <strong>${safe(value)}</strong>
      </article>
    `).join('');
  }

  function formatCurrency(value) {
    return new Intl.NumberFormat(undefined, { style: 'currency', currency: 'USD', maximumFractionDigits: 2 }).format(Number(value || 0));
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
        <thead>
          <tr>
            <th>Name</th>
            <th>Email</th>
            <th>Company</th>
            <th>Phone</th>
            <th>Source</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          ${state.contacts.map((c) => `
            <tr>
              <td>${safe(`${c.first_name || ''} ${c.last_name || ''}`.trim() || '(No name)')}</td>
              <td>${safe(c.email || '')}</td>
              <td>${safe(c.company || '')}</td>
              <td>${safe(c.phone || '')}</td>
              <td>${safe(c.source || '')}</td>
              <td class="pcrm-actions">
                <button class="pcrm-btn-link" data-delete-contact="${c.id}">Delete</button>
              </td>
            </tr>
          `).join('')}
        </tbody>
      </table>
    `;
  }

  function renderDeals() {
    const root = qs('#pcrm-funnels-board');
    if (!root) return;

    const stages = Object.keys(state.deal_stages || {});
    if (!stages.length) {
      root.innerHTML = `<p class="pcrm-muted">${safe(emptyText)}</p>`;
      return;
    }

    root.innerHTML = stages.map((stageKey) => {
      const stageName = state.deal_stages[stageKey];
      const deals = state.deals.filter((d) => d.stage === stageKey);
      return `
        <div class="pcrm-pipeline-col">
          <header>
            <h4>${safe(stageName)}</h4>
            <span>${deals.length}</span>
          </header>
          ${deals.length ? deals.map((d) => `
            <article class="pcrm-deal-card">
              <h5>${safe(d.title)}</h5>
              <p>${safe(d.contact_name || d.contact_email || 'No contact')}</p>
              <strong>${safe(formatCurrency(d.value || 0))}</strong>
              <div class="pcrm-inline-actions">
                <select data-change-stage="${d.id}">
                  ${stages.map((opt) => `<option value="${safe(opt)}" ${opt === d.stage ? 'selected' : ''}>${safe(state.deal_stages[opt])}</option>`).join('')}
                </select>
                <button class="pcrm-btn-link" data-delete-deal="${d.id}">Delete</button>
              </div>
            </article>
          `).join('') : `<p class="pcrm-muted">${safe(emptyText)}</p>`}
        </div>
      `;
    }).join('');
  }

  function renderTasks() {
    const root = qs('#pcrm-tasks-list');
    const dashboardRoot = qs('#pcrm-dashboard-tasks');
    if (!root && !dashboardRoot) return;

    if (!state.tasks.length) {
      if (root) root.innerHTML = `<p class="pcrm-muted">${safe(emptyText)}</p>`;
      if (dashboardRoot) dashboardRoot.innerHTML = `<p class="pcrm-muted">${safe(emptyText)}</p>`;
      return;
    }

    const taskHtml = state.tasks.map((t) => `
      <article class="pcrm-task-item">
        <div>
          <h5>${safe(t.title)}</h5>
          <p>${safe(t.description || '')}</p>
          <small>${safe(t.status)} • ${safe(t.priority)} ${t.due_date ? `• due ${safe(new Date(t.due_date).toLocaleString())}` : ''}</small>
        </div>
        <div class="pcrm-inline-actions">
          <select data-change-task-status="${t.id}">
            ${['open', 'in_progress', 'done'].map((s) => `<option value="${s}" ${t.status === s ? 'selected' : ''}>${safe(s)}</option>`).join('')}
          </select>
          <button class="pcrm-btn-link" data-delete-task="${t.id}">Delete</button>
        </div>
      </article>
    `).join('');

    if (root) root.innerHTML = taskHtml;
    if (dashboardRoot) dashboardRoot.innerHTML = state.tasks.slice(0, 6).map((t) => `
      <div class="pcrm-mini-row">
        <span>${safe(t.title)}</span>
        <strong>${safe(t.status)}</strong>
      </div>
    `).join('');
  }

  function renderSmtp() {
    const root = qs('#pcrm-smtp-list');
    const select = qs('#pcrm-email-smtp');
    if (!root && !select) return;

    const defaultTag = i18n.smtpDefaultTag || 'Default';
    if (select) {
      const options = ['<option value="">Use default sender</option>'].concat(
        state.smtp_accounts.map((a) => {
          const label = `${a.label} (${a.from_email})${a.is_default ? ` - ${defaultTag}` : ''}${a.active ? '' : ' - inactive'}`;
          return `<option value="${a.id}">${safe(label)}</option>`;
        })
      );
      select.innerHTML = options.join('');
    }

    if (!root) return;
    if (!state.smtp_accounts.length) {
      root.innerHTML = `<p class="pcrm-muted">No SMTP accounts yet. Add one to send emails.</p>`;
      return;
    }

    root.innerHTML = state.smtp_accounts.map((a) => `
      <article class="pcrm-smtp-card ${a.active ? '' : 'is-inactive'}">
        <div>
          <h5>${safe(a.label)} ${a.is_default ? `<span class="pcrm-chip">${safe(defaultTag)}</span>` : ''}</h5>
          <p>${safe(a.from_name || a.from_email)} &lt;${safe(a.from_email)}&gt;</p>
          <small>${safe(`${a.host}:${a.port} • ${a.encryption.toUpperCase()}`)}</small>
        </div>
        <div class="pcrm-inline-actions">
          ${a.is_default ? '' : `<button class="pcrm-btn-link" data-make-default-smtp="${a.id}">Set Default</button>`}
          <button class="pcrm-btn-link" data-delete-smtp="${a.id}">Delete</button>
        </div>
      </article>
    `).join('');
  }

  function renderEmailLogs() {
    const root = qs('#pcrm-email-logs');
    const dashboardRoot = qs('#pcrm-dashboard-emails');
    if (!root && !dashboardRoot) return;

    const list = state.email_logs || [];
    if (!list.length) {
      if (root) root.innerHTML = `<p class="pcrm-muted">${safe(emptyText)}</p>`;
      if (dashboardRoot) dashboardRoot.innerHTML = `<p class="pcrm-muted">${safe(emptyText)}</p>`;
      return;
    }

    if (root) {
      root.innerHTML = `
        <table class="pcrm-table">
          <thead>
            <tr>
              <th>When</th>
              <th>From</th>
              <th>To</th>
              <th>Subject</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            ${list.map((l) => {
              let recipients = '';
              try {
                const parsed = JSON.parse(l.recipients || '[]');
                recipients = Array.isArray(parsed) ? parsed.join(', ') : '';
              } catch (e) {
                recipients = l.recipients || '';
              }
              return `
                <tr>
                  <td>${safe(new Date(l.created_at).toLocaleString())}</td>
                  <td>${safe(l.smtp_label || l.from_email || 'N/A')}</td>
                  <td>${safe(recipients)}</td>
                  <td>${safe(l.subject || '')}</td>
                  <td><span class="pcrm-chip ${l.status === 'sent' ? 'ok' : 'bad'}">${safe(l.status)}</span></td>
                </tr>
              `;
            }).join('')}
          </tbody>
        </table>
      `;
    }

    if (dashboardRoot) {
      dashboardRoot.innerHTML = list.slice(0, 6).map((l) => `
        <div class="pcrm-mini-row">
          <span>${safe(l.subject || '(No subject)')}</span>
          <strong>${safe(l.status)}</strong>
        </div>
      `).join('');
    }
  }

  function fillDealFormHelpers() {
    const contactSelect = qs('#pcrm-deal-contact');
    if (contactSelect) {
      const options = ['<option value="">Select contact</option>'].concat(
        state.contacts.map((c) => {
          const name = `${c.first_name || ''} ${c.last_name || ''}`.trim() || c.email;
          return `<option value="${c.id}">${safe(name)} (${safe(c.email)})</option>`;
        })
      );
      contactSelect.innerHTML = options.join('');
    }

    const stageSelect = qs('#pcrm-deal-stage');
    if (stageSelect) {
      stageSelect.innerHTML = Object.entries(state.deal_stages || {}).map(([value, label]) => (
        `<option value="${safe(value)}">${safe(label)}</option>`
      )).join('');
    }
  }

  function bindNavigation() {
    const app = qs('#pcrm-app');
    if (!app) return;
    qsa('.pcrm-nav-btn', app).forEach((btn) => {
      btn.addEventListener('click', () => {
        const section = btn.getAttribute('data-section');
        qsa('.pcrm-nav-btn', app).forEach((b) => b.classList.remove('is-active'));
        qsa('.pcrm-section', app).forEach((s) => s.classList.remove('is-active'));
        btn.classList.add('is-active');
        const target = qs(`.pcrm-section[data-section="${section}"]`, app);
        if (target) target.classList.add('is-active');
      });
    });
  }

  function serializeForm(form) {
    const data = {};
    const fd = new FormData(form);
    fd.forEach((value, key) => {
      if (typeof value === 'string') data[key] = value.trim();
    });

    // Preserve unchecked checkboxes as false-ish values where relevant.
    qsa('input[type="checkbox"]', form).forEach((input) => {
      if (!fd.has(input.name)) data[input.name] = '';
    });
    return data;
  }

  async function refreshAll() {
    setLoadingPlaceholders();
    const bootstrap = await api('bootstrap');
    state.metrics = bootstrap.metrics || {};
    state.contacts = bootstrap.contacts || [];
    state.deals = bootstrap.deals || [];
    state.tasks = bootstrap.tasks || [];
    state.smtp_accounts = bootstrap.smtp_accounts || [];
    state.email_logs = bootstrap.email_logs || [];
    state.deal_stages = bootstrap.deal_stages || {};
    renderAll();
  }

  function renderAll() {
    renderMetrics();
    renderContacts();
    renderDeals();
    renderTasks();
    renderSmtp();
    renderEmailLogs();
    fillDealFormHelpers();
  }

  function bindForms() {
    const contactForm = qs('#pcrm-contact-form');
    if (contactForm) {
      contactForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        try {
          await api('contacts', 'POST', serializeForm(contactForm));
          contactForm.reset();
          await refreshAll();
          toast(i18n.saved || 'Saved.');
        } catch (err) {
          toast(err.message, 'bad');
        }
      });
    }

    const dealForm = qs('#pcrm-deal-form');
    if (dealForm) {
      dealForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const payload = serializeForm(dealForm);
        if (!payload.contact_id) {
          toast('Select a contact for this deal.', 'bad');
          return;
        }
        try {
          await api('deals', 'POST', payload);
          dealForm.reset();
          await refreshAll();
          toast(i18n.saved || 'Saved.');
        } catch (err) {
          toast(err.message, 'bad');
        }
      });
    }

    const taskForm = qs('#pcrm-task-form');
    if (taskForm) {
      taskForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const payload = serializeForm(taskForm);
        if (payload.due_date) payload.due_date = payload.due_date.replace('T', ' ');
        try {
          await api('tasks', 'POST', payload);
          taskForm.reset();
          await refreshAll();
          toast(i18n.saved || 'Saved.');
        } catch (err) {
          toast(err.message, 'bad');
        }
      });
    }

    const smtpForm = qs('#pcrm-smtp-form');
    if (smtpForm) {
      smtpForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const payload = serializeForm(smtpForm);
        payload.is_default = payload.is_default ? 1 : 0;
        payload.active = payload.active ? 1 : 0;
        try {
          await api('smtp', 'POST', payload);
          smtpForm.reset();
          const port = qs('input[name="port"]', smtpForm);
          if (port) port.value = '587';
          const active = qs('input[name="active"]', smtpForm);
          if (active) active.checked = true;
          await refreshAll();
          toast(i18n.saved || 'Saved.');
        } catch (err) {
          toast(err.message, 'bad');
        }
      });
    }

    const emailForm = qs('#pcrm-email-form');
    if (emailForm) {
      emailForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const payload = serializeForm(emailForm);
        if (!payload.smtp_account_id) delete payload.smtp_account_id;
        try {
          await api('emails/send', 'POST', payload);
          emailForm.reset();
          await refreshAll();
          toast(i18n.sendOk || 'Email sent.');
        } catch (err) {
          toast(err.message, 'bad');
        }
      });
    }

    const leadForms = qsa('.pcrm-lead-form');
    leadForms.forEach((form) => {
      form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const msg = qs('.pcrm-lead-msg', form);
        const payload = serializeForm(form);
        payload.source = form.getAttribute('data-source') || payload.source || 'Website Lead Form';
        try {
          const res = await fetch(leadUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload),
          });
          if (!res.ok) {
            throw new Error('Lead submit failed');
          }
          form.reset();
          if (msg) msg.textContent = i18n.leadSuccess || 'Thanks!';
        } catch (err) {
          if (msg) msg.textContent = i18n.leadError || 'Please try again.';
        }
      });
    });
  }

  function bindDynamicActions() {
    document.addEventListener('click', async (e) => {
      const deleteContactId = e.target?.getAttribute?.('data-delete-contact');
      if (deleteContactId) {
        try {
          await api(`contacts/${deleteContactId}`, 'DELETE');
          await refreshAll();
          toast(i18n.deleted || 'Deleted.');
        } catch (err) {
          toast(err.message, 'bad');
        }
      }

      const deleteDealId = e.target?.getAttribute?.('data-delete-deal');
      if (deleteDealId) {
        try {
          await api(`deals/${deleteDealId}`, 'DELETE');
          await refreshAll();
          toast(i18n.deleted || 'Deleted.');
        } catch (err) {
          toast(err.message, 'bad');
        }
      }

      const deleteTaskId = e.target?.getAttribute?.('data-delete-task');
      if (deleteTaskId) {
        try {
          await api(`tasks/${deleteTaskId}`, 'DELETE');
          await refreshAll();
          toast(i18n.deleted || 'Deleted.');
        } catch (err) {
          toast(err.message, 'bad');
        }
      }

      const makeDefaultSmtp = e.target?.getAttribute?.('data-make-default-smtp');
      if (makeDefaultSmtp) {
        try {
          await api(`smtp/${makeDefaultSmtp}/default`, 'POST', {});
          await refreshAll();
          toast(i18n.saved || 'Saved.');
        } catch (err) {
          toast(err.message, 'bad');
        }
      }

      const deleteSmtpId = e.target?.getAttribute?.('data-delete-smtp');
      if (deleteSmtpId) {
        try {
          await api(`smtp/${deleteSmtpId}`, 'DELETE');
          await refreshAll();
          toast(i18n.deleted || 'Deleted.');
        } catch (err) {
          toast(err.message, 'bad');
        }
      }
    });

    document.addEventListener('change', async (e) => {
      const dealId = e.target?.getAttribute?.('data-change-stage');
      if (dealId) {
        try {
          await api(`deals/${dealId}`, 'PUT', { stage: e.target.value });
          await refreshAll();
        } catch (err) {
          toast(err.message, 'bad');
        }
      }

      const taskId = e.target?.getAttribute?.('data-change-task-status');
      if (taskId) {
        try {
          await api(`tasks/${taskId}`, 'PUT', { status: e.target.value });
          await refreshAll();
        } catch (err) {
          toast(err.message, 'bad');
        }
      }
    });

    const searchInput = qs('#pcrm-contact-search');
    if (searchInput) {
      let timer = null;
      searchInput.addEventListener('input', () => {
        window.clearTimeout(timer);
        timer = window.setTimeout(async () => {
          try {
            const results = await api(`contacts?search=${encodeURIComponent(searchInput.value)}`);
            state.contacts = results || [];
            renderContacts();
            fillDealFormHelpers();
          } catch (err) {
            toast(err.message, 'bad');
          }
        }, 280);
      });
    }
  }

  async function initApp() {
    if (!qs('#pcrm-app') && !qsa('.pcrm-lead-form').length) return;

    bindForms();
    bindDynamicActions();

    if (qs('#pcrm-app')) {
      bindNavigation();
      try {
        await refreshAll();
      } catch (err) {
        toast(err.message, 'bad');
      }
    }
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initApp);
  } else {
    initApp();
  }
})();
