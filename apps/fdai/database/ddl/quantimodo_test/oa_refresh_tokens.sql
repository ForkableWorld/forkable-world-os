create table quantimodo_test.oa_refresh_tokens
(
    refresh_token varchar(40)                         not null
        primary key,
    client_id     varchar(80)                         not null,
    user_id       bigint unsigned                     not null,
    expires       timestamp                           null,
    scope         varchar(2000)                       null,
    updated_at    timestamp default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP,
    created_at    timestamp default CURRENT_TIMESTAMP not null,
    deleted_at    timestamp                           null,
    constraint oa_refresh_tokens_client_id_fk
        foreign key (client_id) references quantimodo_test.oa_clients (client_id),
    constraint oa_refresh_tokens_user_id_fk
        foreign key (user_id) references quantimodo_test.wp_users (ID),
    constraint refresh_tokens_client_id_fk
        foreign key (client_id) references quantimodo_test.oa_clients (client_id)
)
    charset = utf8mb3;

