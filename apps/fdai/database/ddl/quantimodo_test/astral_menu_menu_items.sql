create table quantimodo_test.astral_menu_menu_items
(
    id         bigint unsigned auto_increment
        primary key,
    menu_id    bigint unsigned              null,
    name       varchar(255)                 not null,
    class      varchar(255)                 null,
    value      varchar(255)                 null,
    target     varchar(255) default '_self' not null,
    parameters json                         null,
    parent_id  int                          null,
    `order`    int                          not null,
    enabled    tinyint(1)   default 1       not null,
    created_at timestamp                    null,
    updated_at timestamp                    null,
    constraint astral_menu_menu_items_menu_id_foreign
        foreign key (menu_id) references quantimodo_test.astral_menu_menus (id)
            on delete cascade
)
    collate = utf8mb3_unicode_ci;

