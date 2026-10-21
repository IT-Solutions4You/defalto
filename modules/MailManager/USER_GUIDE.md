# Mail Manager – User Guide

Mail Manager lets you read your e-mail inside the CRM and connect messages with your
customers, leads, tickets and opportunities. Newly saved attachments use the configured
attachment storage directory, including an absolute directory outside the public web
directory.

## Getting started

1. Open **Tools → Mail Manager**.
2. Click the gear icon and enter your mail server, user name and password (or sign in
   with Google). The password is stored securely and is not shown again; leave the
   field unchanged to keep it.
3. Choose your **Sent** folder so messages you send from the CRM are also stored there.

## Working with mail

- **Folders:** Inbox, Sent and Trash are shown at the top. All other folders follow,
  including subfolders, shown with their full path (for example `Archives/2026`).
- **Message list:** the newest messages are shown first. Use the arrows to move between
  pages; the counter shows which messages you see (for example `1 - 20 of 350`).
- **Search:** type a word, choose where to search (subject, sender, body, …) and click
  **Search**. Results are also listed newest first.
- **Open a message:** click it. Remote images are blocked until you click **Allow**.
- **Actions:** mark as read or unread, move to another folder, delete, print.
- **Reply, Reply all, Forward:** open the CRM e-mail composer with the original message.
- **Attachments:** click an attachment to download it.

## Connecting mail with the CRM

When the sender is known in the CRM, the matching records are shown next to the message.
You can link the message to a record, add it as a comment, or create a new Contact,
Organization, Lead, Ticket or Opportunity from it. Linked messages appear in the
record's e-mail history. With a record linked, **Actions → Add Event** creates a calendar
event from the message: the subject and text are filled in, the date and time start at
now in your time zone, and the event belongs to the linked organization, person or record
(the message is linked to the event too).

## Mail Manager Pro

With an active Defalto Membership, the same menu entry opens Mail Manager Pro. Without
Membership, or after it ends, Mail Manager works exactly as described above and nothing is
lost. Without Membership a small **Pro** button explains what Pro offers, and a Pro page opened
from a bookmark or from Settings shows what Pro brings, with the Pro offer, a link to the Installer
and — for the administrator — the stored Pro mail that can be deleted.
- **Add Event in Basic** fills the event description with the readable text of the message, without
  its HTML.

- **Your mailbox is taken over automatically.** The account you use in Mail Manager appears
  in Pro without entering the password again; the last 90 days are loaded first, older mail
  with **Load older**.
- **Microsoft 365 and Google mailboxes:** choose **Add mailbox**, the provider and follow the
  steps: register an application with the provider (the wizard shows the redirect URI),
  enter its client ID and secret, click **Sign in**, allow access in the new window, close
  it, click **Check sign-in** and **Connect mailbox**. The mailbox becomes your personal
  mailbox; mail is sent through the CRM outgoing server. Shared mailboxes use IMAP with a
  password.
- **Opening a conversation** shows the customer's latest message expanded – the one the list shows. Replies of
  yours sent after it appear above it as thin one-line bars (sender, start of the text, time); click a bar to
  open it.
- **Keyboard shortcuts** in the inbox (not while typing, with the composer, a window or My settings
  open): **Delete** deletes the ticked conversations, otherwise the open one (with **Undo**),
  **↑ / ↓** open the previous / next conversation of the list, **R** replies.
- **Restore / Not spam** brings a deleted or spammed conversation back: received mail returns to the
  Inbox, your own sent replies and drafts return to the Sent folder they came from. When the mail
  server refuses it, the conversation stays where it was, shows again and a message says why.
- **Conversations** group replies together. Mark them done, assign them to a colleague, set
  a priority and labels. New mail arrives in the background every 15 minutes or with
  **Refresh**.
- **Shared mailboxes** (for example support@) are set up by the administrator in
  Settings → Mail Manager Pro, can have their own outgoing server and are visible to their
  members only. You can give a deputy access to your own mailbox for a limited time.
- **Writing mail:** compose, reply, reply all and forward; drafts are saved in your
  mailbox. Sent mail is stored in the CRM e-mail history and in the mailbox Sent folder. Esc
  closes the composer (it asks first when you have written something).
- **Attachment preview:** a click on an image or PDF attachment opens a preview window;
  the arrows (or ← / →) move between the attachments of the message, **Download** saves the
  shown one. Other files download. Attachments of a mail you just sent from the CRM are
  available after the Sent folder synchronizes (a few minutes).
- **Opens and clicks:** mail you sent from the CRM shows "Opened N× · Clicks N" next to its
  time, the last opening in the tooltip. The numbers are indicative only: a mail client that
  blocks images does not report an opening, and some (Apple Mail Privacy Protection) open
  every mail automatically.
- **Templates:** with EMAIL Maker installed, the composer offers its templates for the
  record the conversation is about (and the templates without a module). The chosen template
  is filled for that record and the recipient and goes above your signature; the quoted
  original stays below. It replaces the subject of a new mail only; its documents are added as
  attachments (you can remove them before sending).
- **Drag & drop:** drag a conversation (or the ticked ones) onto a folder to move it — with
  the usual "Undo" — or onto a label in the sidebar to label it.
- **Errors while writing:** when a mail, a scheduled mail or a draft cannot be saved or sent (a missing
  recipient, subject or text, an invalid time, too large attachments, a refusal of the server), the reason
  shows in the writing window above its buttons and the field to fix is marked red. It goes away once you
  change something or try again.
- **Undo Send:** in My settings → Undo after Send choose 5, 10 or 20 seconds. A sent mail then waits
  that long with an "Undo" card; Undo brings the composer back. Mail with attached files and
  mail sent when you leave the page go out at once.
- **Send later:** **Send later** in the composer picks the date and time (in your CRM time
  zone). The mail waits in **Scheduled** in the sidebar, where you can send it now, move it to
  another time or cancel it. It goes out within a minute or two of the time, through the CRM
  scheduler. Mail you scheduled still goes out when the Membership ends; without Membership it
  can no longer be cancelled, moved or sent now.
- **CRM panel:** see and link contacts, organizations, tickets, opportunities and projects
  of the conversation, create new records with pre-filled data, save attachments as
  documents, reply to a ticket as a comment or close it.
- **Project tasks:** with a project linked, the panel suggests its open tasks. Mail of a
  conversation linked to a task is saved to the task (with the attachments of the latest
  received message as documents) and the same e-mail and documents show on its project.
- **CRM cards:** each value of a linked record is on its own line, a small module icon is
  before the name and the assigned user (or group) closes the card on the right. A record the
  assistant linked by itself says "Linked automatically" (hover for the reason); the record the
  latest mail was logged on as a comment says so too. The suggestion above the mail appears only
  when there is something to decide.
- **AI agents** (administrator, Settings → Mail Manager Pro → AI → Agents):
  - **Keys:** enter the API key of each provider you use (Anthropic, OpenAI, Google) and
    click *Verify and save*. The key is stored encrypted and never shown again.
  - **Agents:** an agent is a provider with a model. Two are ready: *Fast* (sorting, topics,
    data extraction, translation of received mail, reply check) and *Quality* (reply drafts,
    the assistant, summaries, knowledge, writing style). After a key is verified, agents without
    a model get the recommended one; *Use recommended* switches all agents to that provider.
  - **Recommended models:** Anthropic — Haiku 5.5 (fast), Sonnet 5.5 (quality), Opus 5.5
    (deep); OpenAI — GPT-6 Luna (fast), GPT-6 Sol (quality), GPT-6 Astra (deep); Google —
    Gemini 2.5 Flash (fast), Gemini 2.5 Pro (quality). The model list shows these first;
    *Show all models* lists the other current models, with a note when one is expensive or not
    meant for the task.
  - **Tasks:** pick which agent does each task. With *Ask the fallback agent when unsure* an
    unsure sorting is repeated by another agent. *Test* sends a sample e-mail and shows the
    answer, the time and the tokens.
  - **Usage:** tokens per month, task, agent and model; enter your provider's prices per
    1M tokens to see the cost.
    *Load indicative prices* fills the prices from the public OpenRouter catalog (USD per 1M
    tokens, marked "indicative · OpenRouter · date"); your own prices stay unless you tick
    *also replace my own prices*. Only the price list is downloaded — nothing is sent.
  - **Test** of an agent always asks that agent's own provider — during an outage or a
    simulated one it says so instead of answering through the backup.

- **Reply check** (when the administrator switched it on): before a mail goes out the AI
  looks for a mentioned but missing attachment, a wrong salutation or name, an unanswered
  question, an unsuitable tone or a promise (discount, price, deadline, commitment) and asks
  "Send anyway?". It never stops the mail.
- **Check the AI draft:** links, e-mail addresses, phone numbers, IBANs, amounts and
  percentages of an AI draft that are not in the conversation, the linked records or the
  knowledge are highlighted in yellow and listed under the editor — check them before sending.
  The highlight is never sent.
- **What the AI knows from the CRM records:** reply drafts, the assistant, the summary and the
  reply check also read the linked ticket, project task (with its project), project, opportunity
  or lead — up to three records: the description, the solution of a ticket and the whole comment
  history. *Customer comments* (written in the customer portal) count as part of the conversation.
  *Team comments* and *internal notes* give the AI facts (cause, solution, dates, status) — a draft
  for the customer never quotes them and never names colleagues; the assistant answers you from
  them freely. A comment Mail Manager made from a mail of this conversation is not repeated. The
  AI only reads records you may see. A very long history is shortened: the newest comments go
  whole and the older ones as a summary that is written once and again only after a change. The
  reply check also warns when your reply contradicts the history (e.g. a date a later comment
  moved). Administrators switch it off or set a token limit in Settings → AI → Features →
  "Use the description and comments of linked records".
- **Suspicious content for AI:** when a mail tries to give the AI instructions (hidden text,
  "ignore your instructions", a request for the system prompt), the conversation shows
  *Suspicious content for AI*: nothing happens automatically for it and AI drafts carry a
  warning. Read the mail; when it is harmless, click *Mark as safe*.
- **Request radar** (administrator): Settings → AI → Radar lists the requests that keep
  coming (e.g. "price request", "invoice copy") with their count and examples — the ground for
  templates, FAQ and workflows.
- **Anonymization** (administrator): Settings → AI → Features → "Anonymize data for the AI":
  - *Off* — the AI gets the text as it is.
  - *Contact data* — e-mail addresses, IBANs, phone and document numbers go to the AI as
    placeholders ([EMAIL_1], [PHONE_1]) and come back in the answer.
  - *Full* — also the names of people and companies and the addresses known from the
    conversation and the CRM ([PERSON_1], [COMPANY_1], [ADDRESS_1]). The AI then writes
    "Dear [PERSON_1]" and you see the real name. Placeholders are restored only within the
    same answer.
  Solved tickets and similar cases of other customers are always anonymized fully, whatever
  the level. The AI only ever gets the CRM records you can see in your own lists.
- **Backup AI** (administrator): Settings → AI → Backup AI and outages — pick (the Agents tab shows the current backup with a link there) a backup provider (its key
  must be verified on the Agents tab), confirm that during an outage the data also goes to
  it and, optionally, the address for the outage e-mails (empty = all administrators). An
  agent can have its own backup (or none) in its settings. When the main AI fails (outage,
  overload, rejected key, spent credit), the backup takes over everything; after a few
  minutes the main AI is tried again and used as soon as it works. The administrator gets
  one e-mail when the outage starts and one when it ends; the Backup AI and outages tab lists the
  providers, the outages and the calls made by the backup. *Simulate an outage* tries all
  of it for 15 minutes.
- **During an AI outage** (users): a bar on top of the Mail Manager says that the main AI
  is unavailable and the backup runs; drafts, summaries and answers made by the backup say
  so. When both are unavailable, the AI buttons say so — writing and sending mail works as
  always, the sorting of new mail waits for the next run.
- **Digest** (under Workspace in the sidebar): one overview of your whole Workspace, shared
  mailboxes included — *Waiting for you* (the customer wrote last in your conversations),
  *Unassigned in shared mailboxes* (nobody took it yet), *You wait for the customer* (you
  wrote last, or the ticket waits for a response) and *Tasks and tickets in progress*. Each
  row shows its mailbox; a click opens the conversation in the Workspace. A date in red with
  ⚠ means no answer for 5 days or more. With the AI assistant on, a short AI line describes
  each conversation and a few sentences on top say what needs attention first. The lines
  are prepared in the background after a new mail ("The AI line is being prepared…" until
  then); an open digest follows changes by itself within about 3 minutes (new mail, a reply sent
  from another client, a colleague taking a conversation); *Refresh* builds the digest and
  the sentences on top again. Newsletters and
  notifications are left out; a conversation with suspicious content gets no AI line.
- **AI assistant** (when the administrator enabled it): sorts new mail into labels,
  suggests records, summarizes conversations, drafts replies in the customer's language and
  translates messages.
- **My settings:** signature, mailbox and outgoing server, labels and learned rules,
  sharing and the history of automatic actions.
- **Notifications:** in My settings → Notifications you can switch on browser notifications
  of new mail. New unread mail of your Workspace then shows as a notification of the browser
  on any CRM page (up to three one by one, more as one summary); a click opens the
  conversation. The browser asks once for permission and needs an HTTPS address. The inbox
  tab also shows the number of unread conversations in its title.
- **List tabs:** besides All, Unread, Mine, Open, Answered, Unassigned and Done, the
  **Unanswered** tab lists conversations where the customer wrote last and nothing is
  finished yet, and **Not linked** lists conversations without any CRM record. Both leave
  out newsletters, spam and mail from no-reply addresses. In
  My settings → Mail list you choose the tab your inbox opens with.
- **Administrators** get a short setup wizard on the first start and can delete the mail
  stored by Pro in Settings (also after the Membership ended).

## Tips

- Folder counts and the list refresh automatically; use the refresh icon to reload now.
- If a folder seems empty, check that you opened the right subfolder (full path).
