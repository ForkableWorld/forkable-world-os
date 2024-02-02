create table quantimodo_test.password_resets
(
    email      varchar(255)                        not null,
    token      varchar(255)                        not null,
    created_at timestamp default CURRENT_TIMESTAMP not null,
    updated_at timestamp default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP,
    deleted_at timestamp                           null,
    client_id  varchar(255)                        null
)
    charset = utf8mb3;

create index password_resets_email_index
    on quantimodo_test.password_resets (email);

create index password_resets_token_index
    on quantimodo_test.password_resets (token);

