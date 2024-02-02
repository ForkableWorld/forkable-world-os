create table quantimodo_test.tracking_reminder_notifications
(
    id                   int unsigned auto_increment
        primary key,
    tracking_reminder_id int unsigned                            not null,
    created_at           timestamp default CURRENT_TIMESTAMP     not null,
    updated_at           timestamp default CURRENT_TIMESTAMP     not null on update CURRENT_TIMESTAMP,
    deleted_at           timestamp                               null,
    user_id              bigint unsigned                         not null,
    notified_at          timestamp                               null comment 'UTC time when the notification was sent.',
    received_at          timestamp                               null,
    client_id            varchar(255)                            null,
    variable_id          int unsigned                            not null,
    notify_at            timestamp default '0000-00-00 00:00:00' not null comment 'UTC time at which user should be notified.',
    user_variable_id     int unsigned                            not null,
    constraint notify_at_tracking_reminder_id_uindex
        unique (notify_at, tracking_reminder_id),
    constraint tracking_reminder_notifications_client_id_fk
        foreign key (client_id) references quantimodo_test.oa_clients (client_id),
    constraint tracking_reminder_notifications_tracking_reminders_id_fk
        foreign key (tracking_reminder_id) references quantimodo_test.tracking_reminders (id)
            on update cascade on delete cascade,
    constraint tracking_reminder_notifications_user_id_fk
        foreign key (user_id) references quantimodo_test.wp_users (ID)
            on delete cascade,
    constraint tracking_reminder_notifications_user_variables_id_fk
        foreign key (user_variable_id) references quantimodo_test.user_variables (id)
            on update cascade on delete cascade,
    constraint tracking_reminder_notifications_variables_id_fk
        foreign key (variable_id) references quantimodo_test.variables (id)
            on update cascade on delete cascade
)
    charset = utf8mb3;

create index tracking_reminder_notifications_variable_id_fk
    on quantimodo_test.tracking_reminder_notifications (variable_id);

