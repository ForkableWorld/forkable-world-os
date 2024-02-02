create table quantimodo_test.user_follower
(
    id           int unsigned auto_increment
        primary key,
    following_id bigint unsigned not null,
    follower_id  bigint unsigned not null,
    accepted_at  timestamp       null,
    created_at   timestamp       null,
    updated_at   timestamp       null
)
    collate = utf8mb3_unicode_ci;

create index user_follower_accepted_at_index
    on quantimodo_test.user_follower (accepted_at);

create index user_follower_follower_id_index
    on quantimodo_test.user_follower (follower_id);

create index user_follower_following_id_index
    on quantimodo_test.user_follower (following_id);

