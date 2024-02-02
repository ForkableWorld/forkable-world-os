create table quantimodo_test.love_reactants
(
    id         bigint unsigned auto_increment
        primary key,
    type       varchar(255) not null,
    created_at timestamp    null,
    updated_at timestamp    null
)
    collate = utf8mb3_unicode_ci;

create index love_reactants_type_index
    on quantimodo_test.love_reactants (type);

