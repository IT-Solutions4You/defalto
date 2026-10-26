# Appointments changelog

## 1.5 - 2026-10-26

- Prevents users without edit permission from rescheduling appointments in the calendar.
- Fixed saving an appointment whose subject contains a slash, for example a web address. The appointment is now saved completely, including its relation to the organization and its change history.
- Invitation e-mails are prepared only when at least one user is invited.
- Invitation calendar files (.ics) now escape special characters in the subject, description and location.
