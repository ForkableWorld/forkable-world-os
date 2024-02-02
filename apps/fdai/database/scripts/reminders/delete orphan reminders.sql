SELECT
 variable_id,
user_id,
created_at
 FROM tracking_reminders
WHERE variable_id not in (SELECT id FROM variables)
GROUP BY variable_id;

DELETE tracking_reminders FROM tracking_reminders
WHERE variable_id not in (SELECT id FROM variables);

SELECT
 tracking_reminder_id,
user_id,
created_at
 FROM tracking_reminder_notifications
WHERE tracking_reminder_id not in (SELECT id FROM tracking_reminders)
GROUP BY tracking_reminder_id;

DELETE tracking_reminder_notifications FROM tracking_reminder_notifications
WHERE tracking_reminder_id not in (SELECT id FROM tracking_reminders);