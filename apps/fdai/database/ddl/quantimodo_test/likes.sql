create table quantimodo_test.likes
(
    id            bigint unsigned auto_increment
        primary key,
    user_id       bigint unsigned not null comment 'user_id',
    likeable_type varchar(255)    not null,
    likeable_id   bigint unsigned not null,
    created_at    timestamp       null,
    updated_at    timestamp       null
)
    collate = utf8mb3_unicode_ci;

create index likes_likeable_type_likeable_id_index
    on quantimodo_test.likes (likeable_type, likeable_id);

create index likes_user_id_index
    on quantimodo_test.likes (user_id);

