create table quantimodo_test.astral_menu_menus
(
    id               bigint unsigned auto_increment
        primary key,
    name             varchar(255)    not null,
    slug             varchar(255)    not null,
    locale           varchar(255)    not null,
    created_at       timestamp       null,
    updated_at       timestamp       null,
    locale_parent_id bigint unsigned null,
    constraint astral_menu_menus_slug_locale_unique
        unique (slug, locale),
    constraint menus_locale_parent_id_locale_unique
        unique (locale_parent_id, locale),
    constraint menus_locale_parent_id_foreign
        foreign key (locale_parent_id) references quantimodo_test.astral_menu_menus (id)
)
    collate = utf8mb3_unicode_ci;

