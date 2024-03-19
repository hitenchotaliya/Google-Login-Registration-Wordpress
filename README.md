# Google-Login-Registration-Wordpress
This plugin allow you to login with google.
**Switch to master**

**Update Your ClientID and ClientSecret if you dont have then follow below steps:**
Here are the step-by-step instructions:

**1.Create a Project:**

**Go to the Google Cloud Console.**
Click on "Select a project" in the top navigation bar, then click on the "+ New Project" button.
Enter a name for your project and click "Create".

**Enable APIs:**
In the Cloud Console, navigate to the "APIs & Services" > "Library" section.
Search for the API you want to enable (e.g., Google Drive API, Google Calendar API) and click on it.
Click the "Enable" button to enable the API for your project.

**Set up OAuth Consent Screen:**
In the Cloud Console, navigate to "APIs & Services" > "OAuth consent screen".
Choose the user type that best fits your scenario (Internal or External) and click "Create".
Fill out the required information such as Application Name, User Support Email, etc.
Add the necessary scopes for your application.
Save and continue.

**Create OAuth 2.0 Credentials:**
In the Cloud Console, navigate to "APIs & Services" > "Credentials".
Click on "Create credentials" and choose "OAuth client ID".
Choose the application type (Web application, Android, iOS, etc.).
Enter the appropriate information such as name and authorized redirect URIs.
Click "Create" to generate the Client ID and Client Secret.

**Note your Client ID and Client Secret:**
After creating the OAuth 2.0 client ID, you will be provided with your Client ID and Client Secret.
Make sure to securely store these credentials, as they are used to authenticate requests to Google services.


_**That's it! You've now successfully generated a Google Client ID and Client Secret**_
