# Gmail Contacts Management Module
This is the code repository for Gmail Contacts Management Module.

## What is this module about?
This module is about getting gmail contacts or other contacts in google through Google API authentication. It offers two options:
- Get contacts stored in gmail address book
- Get contacts that are not saved in the address book but user have interacted with.

#Preequisites
Create a OAUTH2 client id and secret for your aplication in the site: https://console.cloud.google.com/

## Instructions
Install module and locate block in content area. Set configurations values through Google Api configuration in route '/admin/config/google_api/settings' in order to connect with People API(Google Api).
The endpoints used in this module are:
- Gmail contacts(Address book)
https://www.googleapis.com/auth/contacts.readonly
- Gmail other contacts
https://www.googleapis.com/auth/contacts.other.readonly

Select option "Address book" or "Other contacts" in Google Api Configuration form if you want to get contacts from one of these categories.

![Gmail contacts module - video](https://user-images.githubusercontent.com/39375370/139271864-48e55b22-7cb9-426a-9466-1879e5b439a5.gif)
