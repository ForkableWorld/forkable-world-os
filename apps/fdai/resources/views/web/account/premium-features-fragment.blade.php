@if(empty(Auth::user()->last_four))
    <h3>Please support the development of the {{app_display_name()}} platform and help us abolish suffering by signing up for {{app_display_name()}} Plus!</h3>
    <h4>With {{app_display_name()}} Plus, you'll enjoy these awesome features and more:</h4>
    <ul>
        <li><b>Import Data from Other Apps &amp; Devices</b> - Easily import your data from Fitbit, Withings, Jawbone, Facebook, Rescuetime, Sleep as Android, MoodiModo, Github, Google Calendar, Facebook, Runkeeper, and even the weather!</li>
        <li><b>Discover Hidden Causes of Suffering</b> - The {{app_display_name()}} Analytics Engine will identify the foods, treatments, and other factors most likely to improve or exacerbate your symptoms!</li>
        <li><b>Secure Cloud Storage</b> - Never worry about losing your self-tracking data as it will be highly encrypted and backed up in multiple secure databases. </li>
        <li><b>Privacy</b> - We will never share your data without your explicit permission. </li>
        <li><b>Sync Data Across Devices</b> - Any of your {{app_display_name()}}-supported apps will be able to automatically sync from any other app.</li>
    </ul>
@endif
