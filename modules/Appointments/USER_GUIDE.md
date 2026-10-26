# Appointments

Appointments provides the CRM calendar and supports creating calendar records automatically from workflows.

The Appointments record Summary displays the available standard widgets, such as key fields, related documents, comments, or related appointments according to the installed relations and the current user's permissions.

When an appointment with invited users is saved, each invited user receives an email with the appointment attached as a calendar (.ics) file.

Appointments can be rescheduled from the calendar by dragging them to another date or time or by resizing their duration. These changes are saved only when the current user has permission to edit the selected appointment; otherwise, the calendar restores the original schedule.

## Create Calendar Record workflow action

Configure the subject, description, status, type, owner, start and end values, and all-day behavior in the workflow task. Click Subject or Description to open the value window, where you can enter text and insert variables from the workflow's source record.

Use **Add field** under Additional fields to map optional standard fields or custom Appointments fields. Each destination field can receive fixed text, source-record variables, a single source field, or a workflow expression. Open the mapped value window and use its Variables selector while editing raw text. For controls such as picklists, use the pencil button to open this window. You can combine text and multiple variables in one mapping. The same destination field can be mapped only once.

Text values can contain source-field variables. The value window inserts PDFMaker-style `$field$` variables, so a subject such as `$firstname$ $lastname$` is filled when the workflow creates the appointment. Existing `$field` values remain supported. Related-record variables available in the workflow editor can be used as well.

Required custom fields continue to appear directly in the task form. If an administrator later removes or disables a mapped field, the workflow skips that obsolete mapping instead of writing to an unavailable field.
