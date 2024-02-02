create table quantimodo_test.oauth_refresh_tokens
(
    id              varchar(100) not null
        primary key,
    access_token_id varchar(100) not null,
    revoked         tinyint(1)   not null,
    expires_at      datetime     null
)
    collate = utf8mb3_unicode_ci;

create index oauth_refresh_tokens_access_token_id_index
    on quantimodo_test.oauth_refresh_tokens (access_token_id);

