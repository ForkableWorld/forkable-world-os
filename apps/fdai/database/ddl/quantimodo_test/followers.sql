create table quantimodo_test.followers
(
    id               int unsigned auto_increment
        primary key,
    user_id          int       not null,
    followed_user_id int       not null,
    created_at       timestamp null,
    updated_at       timestamp null
)
    collate = utf8mb3_unicode_ci;

create index followers_followed_user_id_index
    on quantimodo_test.followers (followed_user_id);

create index followers_user_id_index
    on quantimodo_test.followers (user_id);

