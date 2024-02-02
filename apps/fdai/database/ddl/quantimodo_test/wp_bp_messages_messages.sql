create table quantimodo_test.wp_bp_messages_messages
(
    id         bigint auto_increment
        primary key,
    thread_id  bigint                              not null,
    sender_id  bigint                              not null,
    subject    varchar(200)                        not null,
    message    longtext                            not null,
    date_sent  datetime                            not null,
    updated_at timestamp default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP,
    created_at timestamp default CURRENT_TIMESTAMP not null,
    deleted_at timestamp                           null,
    client_id  varchar(255)                        null
)
    charset = utf8mb3;

create index sender_id
    on quantimodo_test.wp_bp_messages_messages (sender_id);

create index thread_id
    on quantimodo_test.wp_bp_messages_messages (thread_id);

