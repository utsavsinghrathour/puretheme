## Pure CRM Lite

Frontend-first WordPress CRM plugin with:

- Contacts and notes
- Custom funnels with stage columns
- Drag-and-drop Kanban deal stage updates
- Task assignment to contacts with inline edit flow
- Follow-up notifications (due/overdue)
- Multi-SMTP email sending with default sender + per-email sender selection

### Shortcodes

- Main CRM app: `[pure_crm]`
- Public lead form: `[pure_crm_lead_form]`

### Frontend usage notes

1. Add a page and place `[pure_crm]`.
2. Add at least one funnel (name + comma-separated stages).
3. Create contacts first so deals/tasks can be assigned cleanly.
4. In **Funnels**, use Kanban drag/drop to move deal stages.
5. In **Tasks**, select a contact and use **Edit** for updates.
6. Set `Next follow up` on deals and `Due date` on tasks to get notification prompts.

### SMTP workflow

1. Add one or more SMTP accounts in the SMTP section.
2. Mark one account as default.
3. In Email composer, choose either default sender or a specific account.
