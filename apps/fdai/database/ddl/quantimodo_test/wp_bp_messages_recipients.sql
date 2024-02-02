create table quantimodo_test.wp_bp_messages_recipients
(
    id           bigint auto_increment
        primary key,
    user_id      bigint unsigned                      not null,
    thread_id    bigint                               not null,
    unread_count int        default 0                 not null,
    sender_only  tinyint(1) default 0                 not null,
    is_deleted   tinyint(1) default 0                 not null,
    updated_at   timestamp  default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP,
    created_at   timestamp  default CURRENT_TIMESTAMP not null,
    deleted_at   timestamp                            null,
    client_id    varchar(255)                         null,
    constraint wp_bp_messages_recipients_wp_users_ID_fk
        foreign key (user_id) references quantimodo_test.wp_users (ID)
)
    charset = utf8mb3;

create index is_deleted
    on quantimodo_test.wp_bp_messages_recipients (is_deleted);

create index sender_only
    on quantimodo_test.wp_bp_messages_recipients (sender_only);

create index thread_id
    on quantimodo_test.wp_bp_messages_recipients (thread_id);

create index unread_count
    on quantimodo_test.wp_bp_messages_recipients (unread_count);

create index user_id
    on quantimodo_test.wp_bp_messages_recipients (user_id);

