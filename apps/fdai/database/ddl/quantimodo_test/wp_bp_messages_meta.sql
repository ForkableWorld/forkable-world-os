create table quantimodo_test.wp_bp_messages_meta
(
    id         bigint auto_increment
        primary key,
    message_id bigint                              not null,
    meta_key   varchar(255)                        null,
    meta_value longtext                            null,
    updated_at timestamp default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP,
    created_at timestamp default CURRENT_TIMESTAMP not null,
    deleted_at timestamp                           null,
    client_id  varchar(255)                        null
)
    charset = utf8mb3;

create index message_id
    on quantimodo_test.wp_bp_messages_meta (message_id);

create index meta_key
    on quantimodo_test.wp_bp_messages_meta (meta_key(191));

