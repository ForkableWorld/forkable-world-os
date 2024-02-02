create table quantimodo_test.oauth_access_tokens
(
    id         varchar(100) not null
        primary key,
    user_id    bigint       null,
    client_id  int unsigned not null,
    name       varchar(255) null,
    scopes     text         null,
    revoked    tinyint(1)   not null,
    created_at timestamp    null,
    updated_at timestamp    null,
    expires_at datetime     null
)
    collate = utf8mb3_unicode_ci;

create index oauth_access_tokens_user_id_index
    on quantimodo_test.oauth_access_tokens (user_id);

