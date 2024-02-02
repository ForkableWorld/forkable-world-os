create table quantimodo_test.love_reaction_types
(
    id         bigint unsigned auto_increment
        primary key,
    name       varchar(255) not null,
    mass       tinyint      not null,
    created_at timestamp    null,
    updated_at timestamp    null
)
    collate = utf8mb3_unicode_ci;

create index love_reaction_types_name_index
    on quantimodo_test.love_reaction_types (name);

