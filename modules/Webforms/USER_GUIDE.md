# Webforms User Guide

Webforms publish selected CRM fields as an embeddable form. A successful submission creates a record in the configured target module, assigns it to the configured owner or round-robin owner, uploads supported documents, and then opens the configured return URL or returns a JSON result.

## Creating and publishing a form

1. Open Settings and select Webforms.
2. Choose the target module, owner, fields, defaults, required fields, and optional CAPTCHA or return URL.
3. Copy the generated form markup to the website where visitors will use it.

## Submission validation

Required fields must contain a value. Dropdown and multi-select fields accept only options currently available in the CRM. These checks run on the CRM server even if browser-side validation is bypassed. Invalid submissions do not create a CRM record and return an error to the caller or configured return URL.

If a valid submission fails, confirm that the webform is enabled, its public ID is unchanged, the selected fields still exist in the target module, and CAPTCHA configuration is valid when CAPTCHA is enabled.
