create table quantimodo_test.device_tokens
(
    device_token                                      varchar(255)                        not null
        primary key,
    created_at                                        timestamp default CURRENT_TIMESTAMP not null,
    updated_at                                        timestamp default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP,
    deleted_at                                        timestamp                           null,
    user_id                                           bigint unsigned                     not null,
    number_of_waiting_tracking_reminder_notifications int unsigned                        null comment 'Number of notifications waiting in the reminder inbox',
    last_notified_at                                  timestamp                           null,
    platform                                          varchar(255)                        not null,
    number_of_new_tracking_reminder_notifications     int unsigned                        null comment 'Number of notifications that have come due since last notification',
    number_of_notifications_last_sent                 int unsigned                        null comment 'Number of notifications that were sent at last_notified_at batch',
    error_message                                     varchar(255)                        null,
    last_checked_at                                   timestamp                           null,
    received_at                                       timestamp                           null,
    server_ip                                         varchar(255)                        null,
    server_hostname                                   varchar(255)                        null,
    client_id                                         varchar(255)                        null,
    constraint device_tokens_client_id_fk
        foreign key (client_id) references quantimodo_test.oa_clients (client_id),
    constraint device_tokens_user_id_fk
        foreign key (user_id) references quantimodo_test.wp_users (ID)
)
    charset = utf8mb3;

create index index_user_id
    on quantimodo_test.device_tokens (user_id);

