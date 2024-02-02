<div class="article-content"  style="font-family: sans-serif">

    <h3>{{app_display_name()}} uses high-security standards to protect customers’ data and ensure users’ privacy. The security is implemented for data at rest and data in transport.</h3>
    <h2>Transmission Security</h2>
    <ul>
        <li>All the data served over the {{app_display_name()}} API uses HTTPS.</li>
        <li>We audit our security setup to ensure that the certificates we serve are up to date.</li>
        <li>We force HTTPS for all connection to our API server to ensure that data is always encrypted during the transport from our server to your application.</li>
        <li>It is important that you make sure to use the same methods to ensure that the data is encrypted all the way to the end user.</li>
    </ul>
    <h2>Data Encryption</h2>
    <ul>
        <li>AES 256bit encryption</li>
        <li>Encryption keys are rotated and separated from the database and application servers</li>
        <li>They are stored in a fault-tolerant key management cluster with limited access.</li>
        <li>The master key is kept in a secure vault to ensure a maximum level of security.</li>
    </ul>
    <h2>HIPAA and BAAs</h2>
    <p>{{app_display_name()}} Integration will enter into Business Associate Agreements with covered entities of sub-contractors as we find appropriate depending on the type of data integrations that are necessary. For requests regarding Business Associate Agreements please contact us at {{getHostAppSettings()->additionalSettings->companyEmail}}.</p>
    <h2>Logging</h2>
    <p>All API calls are logged for later review.</p>
    <h1><span style="font-weight: 400;">Detailed Security Policy</span></h1>
    <p><b>OVERVIEW</b></p>
    <p><span style="font-weight: 400;">{{app_display_name()}} focuses on security from the ground up. Our Data Center (managed by Amazon Web Services) is SAS 70 Type II certified, SSAE16 (SOC 2) Compliant, and features proximity security badge access and digital security video surveillance. Our server network can only be accessed via SSL VPN with public key authentication or via Two-factor Authentication over SSL. We run monthly Qualys Vulnerability Assessments on our production environment. Additionally, our network can only be accessed via multi-factor authentication, and all access to our web portal is secured over HTTPS using SSL 256-bit encryption. Additionally, all staff members with access to Client Data receive certification as a HIPAA Privacy Associate.</span></p>
    <p><b>DEFINITION OF TERMS &amp; SYSTEM USERS:</b></p>
    <p><span style="font-weight: 400;">Client — A customer of {{app_display_name()}}.</span></p>
    <p><span style="font-weight: 400;">User — An individual with access to a {{app_display_name()}} Application.</span></p>
    <p><span style="font-weight: 400;">Admin — A Client User with the capability of viewing and managing certain aspect of Client’s {{app_display_name()}} Account.</span></p>
    <p><span style="font-weight: 400;">Member — A Client User whose account is provisioned through Client’s Web Portal. A Member cannot log in or otherwise access any {{app_display_name()}} Application directly. All Member Data stored in our system is de-identified in compliance with the HIPAA “Safe Harbor” de-identification standard.</span></p>
    <p><span style="font-weight: 400;">Developer — A User that can create vendor applications in {{app_display_name()}} for the purpose integrating mobile health apps and/or devices.</span></p>
    <p><span style="font-weight: 400;">{{app_display_name()}} Admin — A {{app_display_name()}} employee with access to managing a Client’s account.</span></p>
    <p><b>DATA CENTER AND HARDWARE</b></p>
    <p><span style="font-weight: 400;">All {{app_display_name()}} application and database servers are physically managed by Amazon Web Services in secure data centers within the United States. Our security procedures utilize industry best practices from sources including The Center for Internet Security (CIS), Microsoft, Red Hat and more. All data center facilities are certified SSAE 16 (SOC 2) Compliant and have 24/7 physical security of data centers and Network Operations Center monitoring.</span></p>
    <p><b><i>Physical Security</i></b></p>
    <p><span style="font-weight: 400;">All servers are located in a Data Centers managed by Amazon Web Services within the United States. Physical access is controlled both at the perimeter and at building ingress points by professional security staff utilizing video surveillance, intrusion detection systems, and other electronic means. {{app_display_name()}} employees do not have access to physical server hardware.</span></p>
    <p><b><i>Data Access and Server Management Security</i></b></p>
    <p><span style="font-weight: 400;">{{app_display_name()}} has IPSec VPN connections to our hosting environment. Only select {{app_display_name()}} employees are able to access the server network.</span></p>
    <p><b><i>Environmental Safeguards</i></b></p>
    <p><span style="font-weight: 400;">All Amazon Web Services data centers are equipped with automatic fire detection and suppression (either wet-pipe, double-interlocked pre-action, or gaseous sprinkler systems), climate and temperature controls, fully redundant uninterruptible Power Supplies (UPS), and generators to provide backup power for each physical site.</span></p>
    <p><b>DATA STORAGE AND BACKUPS</b></p>
    <p><span style="font-weight: 400;">All Member Data stored in our system is de-identified in compliance with the HIPAA “Safe Harbor” de-identification standard, and all data is encrypted at rest using 256-bit AES. {{app_display_name()}} production database servers are replicated across multiple availability zones. Database backups use a fully disk-based solution (disk-to-disk) and full system backups, are performed daily, weekly, and monthly. Daily backups are retained for a minimum of 7 days, weekly backups are retained for a minimum of 4 weeks, monthly backups are retained for 3 years. Backups are stored in multiple geographic availability zones within Amazon Web Services.</span></p>
    <p><b><i>Client Data Policies</i></b></p>
    <p><span style="font-weight: 400;">Client Data includes data stored by Clients in {{app_display_name()}} applications, information about a Client’s usage of the application, data instances in the CRM system that we have access to, or data that the Client has supplied to use for support or implementation. Here are the special considerations we take into account when managing Client Data:</span></p>
    <ol>
        <li style="font-weight: 400;"><span style="font-weight: 400;">Client Data is not to be disclosed outside of {{app_display_name()}}, except to the Client who owns the data or to a Partner who has been contracted by the Client to manage or support their account.</span></li>
        <li style="font-weight: 400;"><span style="font-weight: 400;">Client Data should only be shared using a secure sending method. Approved sending and sharing methods include Dropbox, Google Drive, emailing of encrypted files or use of a Client-provided secure transfer method.</span></li>
        <li style="font-weight: 400;"><span style="font-weight: 400;">Client Data should only be stored temporarily outside of the {{app_display_name()}} Application if at all. If there is a need to archive Client Data (for example, data provided by a Client during implementation or training), the data should be stored on a central file server and deleted from any personal computers. This includes report exports, contact lists, and presentations that contain Client information, and Client agreements.</span></li>
        <li style="font-weight: 400;"><span style="font-weight: 400;">Client Data should only be accessed on a need-to-know basis. Specifically, a Client’s account should only be accessed to provide support, troubleshoot a problem with that account, or for supporting the system as a whole.</span></li>
        <li style="font-weight: 400;"><span style="font-weight: 400;">Client Data should never be changed except with the explicit permission of the Client, with the exception of repairing data quality issues.</span></li>
    </ol>
    <p><b><i>Destruction of Server Data</i></b></p>
    <p><span style="font-weight: 400;">In order to maintain system integrity, Client Data that has outlived its use is retained up to 60 days before it is destroyed. The data may remain in our backup files for up to 14 months, as it is our policy to maintain weekly backups for a minimum of 52 weeks before those backups are destroyed. De-identified activity data from Members may be stored in perpetuity for future analysis.</span></p>
    <p><b><i>Disposal of Computers and Other Data</i></b></p>
    <p><span style="font-weight: 400;">Old computers and servers used to store or access client information receive a 7-pass erase that meets the U.S. Department of Defense 5220-22 M standard for erasing magnetic media; the devices are then recycled or resold to manufacturers. Paper information in the office is discarded using a document shredder or a commercial secure document shredding service.</span></p>
    <p><b>Incident Response</b></p>
    <p><span style="font-weight: 400;">{{app_display_name()}} security administrators will be immediately and automatically notified via email if implemented security protocols detect an incident. All other suspected intrusions, suspicious activity, or system unexplained erratic behavior discovered by administrators, users, or computer security personnel must be reported to a security administrator within 1 hour.</span></p>
    <p><span style="font-weight: 400;">Once an incidence is reported, security administrators will immediately begin verifying that an incident occurred and the nature of the incident with the following goals:</span></p>
    <ol>
        <li style="font-weight: 400;"><span style="font-weight: 400;">Maintain or restore business continuity</span></li>
        <li style="font-weight: 400;"><span style="font-weight: 400;">Reduce the incident impact</span></li>
        <li style="font-weight: 400;"><span style="font-weight: 400;">Determine how the attack was performed or the incident happened</span></li>
        <li style="font-weight: 400;"><span style="font-weight: 400;">Develop a plan to improve security and prevent future attacks or incidents</span></li>
        <li style="font-weight: 400;"><span style="font-weight: 400;">Keep management informed of the situation and prosecute any illegal activity</span></li>
    </ol>
    <p><b><i>Determining the Extent of an Incident</i></b></p>
    <p><span style="font-weight: 400;">Security administrators will use forensic techniques including reviewing system logs, looking for gaps in logs, reviewing intrusion detection logs, interviewing witnesses and the incident victim to determine how the incident was caused. Only authorized personnel will perform interviews or examine evidence, and the authorized personnel may vary by situation.</span></p>
    <p><b><i>Notifying Clients of an Incident</i></b></p>
    <p><span style="font-weight: 400;">Clients will be notified via email within one hour upon detection of any incident that compromises access to the service, comprises data, or otherwise affects users. Clients will receive a status update every 4 hours and upon incident resolution.</span></p>
    <p><b>APPLICATION SECURITY</b></p>
    <p><span style="font-weight: 400;">All data transfer and access to {{app_display_name()}} applications will occur only on Port 443 over an HTTPS encrypted connection with 256-bit SSL encryption.</span></p>
    <p><b><i>System Updates and Security Patches</i></b></p>
    <p><span style="font-weight: 400;">As a hosted solution, we regularly improve our system and update security patches. No client resources are needed to perform these updates. Non-critical system updates will be installed at predetermined times (typically 2:00 a.m. Eastern on Thursdays). Critical application updates are performed ad hoc using rolling deployment to maximize system performance and minimize disruption. All updates and patches will be evaluated in a virtual production environment before implementing.</span></p>
    <p><b><i>Vulnerability and Security Testing</i></b></p>
    <p><span style="font-weight: 400;">{{app_display_name()}} performs Qualys Vulnerability Assessments and creates external security reports of our production environment once a month. Additional internal security testing is performed on the testing environment before code is checked into a master repository.</span></p>
    <p><b><i>User Login and Session Security</i></b></p>
    <p><span style="font-weight: 400;">All Member logins and sessions are authenticated via a secure OAuth 2.0 access token.</span></p>
    <p><b><i>Application Password Management</i></b></p>
    <p><span style="font-weight: 400;">Admin passwords must have at least 8 characters with at least one number and one letter.</span></p>
    <p><span style="font-weight: 400;">{{app_display_name()}} Admin passwords must have at least 8 characters with at least one number and one letter, and at minimum either one capital letter and/or one special character.</span></p>
    <p><b>DISASTER RECOVERY</b></p>
    <p><span style="font-weight: 400;">{{app_display_name()}} maintains real time data stores mirrored across multiple geographic availability zones in Amazon Web Services within the United States. In a disaster situation, the full {{app_display_name()}} platform will be recreated and available in a different availability zone within 1hr of disaster declaration.</span></p>
    <p><b>HIPAA &amp; PHI COMPLIANCE</b></p>
    <p><span style="font-weight: 400;">In addition to the above HIPAA compliant policies for data storage and handling, the following procedures are in place to ensure HIPAA compliance:</span></p>
    <ol>
        <li style="font-weight: 400;"><span style="font-weight: 400;">All {{app_display_name()}} employees receive annual HIPAA Business Associate training and certification</span></li>
        <li style="font-weight: 400;"><span style="font-weight: 400;">{{app_display_name()}} web-based applications receive annual internal HIPAA audits</span></li>
    </ol>
    <p><b><i>PHI Handling Policy</i></b></p>
    <p><span style="font-weight: 400;">All {{app_display_name()}} staff members are made aware of relevant external regulations as part of their induction process, and all staff who may come into contact with PHI are trained in our PHI handling processes.</span></p>
    <p><span style="font-weight: 400;">{{app_display_name()}} anonymizes PHI upon receipt and destroys the original except in exceptional circumstances. Where anonymization is not possible (for example for technical reasons or where a product problem can only be recreated using PHI or if the Client specifies the data cannot be anonymized (e.g. if we are investigating a problem on a Client’s workstation), access to the data is restricted and the data is destroyed or returned to the Client as soon as it is no longer needed. Under no circumstances should identified data be added to the company dataset library.</span></p>
    <p><span style="font-weight: 400;">{{app_display_name()}} expects professional integrity of our collaborators, Clients and partners providing PHI to us and will assume that they have obtained the data subject’s consent to use their data in this way.</span></p>
    <p><span style="font-weight: 400;">Where a Business Associate agreement or similar contract relating to PHI is in place, {{app_display_name()}} staff members work under the terms of that agreement. Where no such agreement exists, the {{app_display_name()}} PHI handling policy and process are followed.</span></p>
    <p><span style="font-weight: 400;">{{app_display_name()}} conducts periodic internal audits on compliance with this policy.</span></p>
    <p>on compliance with this policy.</p>

</div>