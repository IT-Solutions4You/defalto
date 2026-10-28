# Appointments changelog

## 1.4 - 2026-10-28

- Monthly repetitions no longer spill into the following month when the selected date is unavailable or a time-zone boundary shifts a first/last weekday rule.
- Dragging or resizing an appointment around midnight now saves the selected calendar date in every time zone.
- Appointment email reminders are now sent at the correct time when the database server uses a non-UTC time zone.
- Calendar popovers and event information now respect record viewing permissions.
- Default event types are protected from deletion by non-administrators, and removing a custom type no longer affects other users who use it.
- Prevents users without edit permission from rescheduling appointments in the calendar.
- Fixed saving an appointment whose subject contains a slash, for example a web address. The appointment is now saved completely, including its relation to the organization and its change history.
- Invitation e-mails are prepared only when at least one user is invited.
- Invitation calendar files (.ics) now escape special characters in the subject, description and location.
