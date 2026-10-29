# Appointments changelog

## 1.4 - 2026-10-29

- All-day appointments now remain on the selected date and no longer expand into adjacent days for users in other time zones.
- Monthly repetitions no longer spill into the following month when the selected date is unavailable or a time-zone boundary shifts a first/last weekday rule.
- Recurring appointments can now be saved when the form does not submit separate date and time controls, including quick-create and all-day flows.
- Dragging or resizing an appointment around midnight now saves the selected calendar date in every time zone.
- Appointment email reminders are now sent at the correct time when the database server uses a non-UTC time zone.
- Calendar popovers and event information now respect record viewing permissions.
- Default event types are protected from deletion by non-administrators, and removing a custom type no longer affects other users who use it.
- Prevents users without edit permission from rescheduling appointments in the calendar.
- Fixed saving an appointment whose subject contains a slash, for example a web address. The appointment is now saved completely, including its relation to the organization and its change history.
- Invitation e-mails are prepared only when at least one user is invited.
- Invitation calendar files (.ics) now escape special characters in the subject, description and location.
