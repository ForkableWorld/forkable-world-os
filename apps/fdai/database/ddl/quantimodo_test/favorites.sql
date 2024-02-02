create table quantimodo_test.favorites
(
    id                bigint unsigned auto_increment
        primary key,
    user_id           bigint unsigned not null comment 'user_id',
    favoriteable_type varchar(255)    not null,
    favoriteable_id   bigint unsigned not null,
    created_at        timestamp       null,
    updated_at        timestamp       null,
    is_public         tinyint(1)      null
)
    collate = utf8mb3_unicode_ci;

create index favorites_favoriteable_type_favoriteable_id_index
    on quantimodo_test.favorites (favoriteable_type, favoriteable_id);

create index favorites_user_id_index
    on quantimodo_test.favorites (user_id);

