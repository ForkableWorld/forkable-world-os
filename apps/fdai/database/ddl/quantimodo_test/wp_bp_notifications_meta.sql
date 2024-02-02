create table quantimodo_test.wp_bp_notifications_meta
(
    id              bigint auto_increment
        primary key,
    notification_id bigint                              not null,
    meta_key        varchar(255)                        null,
    meta_value      longtext                            null,
    updated_at      timestamp default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP,
    created_at      timestamp default CURRENT_TIMESTAMP not null,
    deleted_at      timestamp                           null,
    client_id       varchar(255)                        null
)
    charset = utf8mb3;

create index meta_key
    on quantimodo_test.wp_bp_notifications_meta (meta_key(191));

create index notification_id
    on quantimodo_test.wp_bp_notifications_meta (notification_id);

