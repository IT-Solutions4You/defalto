# Google integration

The Google integration connects a CRM user with Google Contacts and Google Calendar. It can synchronize contacts and appointments and can display supported CRM addresses on Google Maps.

## Connect an account

1. Open the Google integration settings.
2. Select **Sign in with Google**.
3. Complete consent in the popup window.
4. After the popup closes, confirm that the connected Google account is shown in the settings page.

The sign-in request is tied to the current CRM session. If the authorization response is invalid or no longer current, the connection is rejected and the user must start sign-in again.

## Synchronization

- Choose whether Contacts and Appointments are enabled.
- Select the synchronization direction for each area.
- For Contacts, configure field mappings and optionally select a Google contact group.
- For Appointments, select the target Google calendar.
- Use the synchronization button in the module widget or the main Google settings action to run synchronization.

The popup shows created, updated, and deleted record counts after a run. Use **Change user** to connect a different Google account and **Remove synchronization** to disconnect the current synchronization setup.

## Google Maps

Open the Google Map widget from a supported Contacts, Leads, or Accounts record. The map uses the address fields configured for that module. Add or correct the record address if no location is shown.

## Practical tips

- The Google OAuth redirect URL displayed in settings must also be configured in the Google API client.
- Popup blocking must be disabled for the CRM site during account connection.
- A Google refresh token is required for background synchronization; reconnect the account if Google revokes access.
