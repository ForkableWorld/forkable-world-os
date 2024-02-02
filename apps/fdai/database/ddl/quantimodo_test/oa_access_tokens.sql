create table quantimodo_test.oa_access_tokens
(
    access_token varchar(40)                         not null
        primary key,
    client_id    varchar(80)                         not null,
    user_id      bigint unsigned                     not null,
    expires      timestamp                           null,
    scope        varchar(2000)                       null,
    updated_at   timestamp default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP,
    created_at   timestamp default CURRENT_TIMESTAMP not null,
    deleted_at   timestamp                           null,
    constraint oa_access_tokens_client_id_fk
        foreign key (client_id) references quantimodo_test.oa_clients (client_id),
    constraint access_tokens_client_id_fk
        foreign key (client_id) references quantimodo_test.oa_clients (client_id),
    constraint oa_access_tokens_user_id_fk
        foreign key (user_id) references quantimodo_test.wp_users (ID)
)
    charset = utf8mb3;

