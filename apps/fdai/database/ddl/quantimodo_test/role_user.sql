create table quantimodo_test.role_user
(
    id         int unsigned auto_increment
        primary key,
    role_id    int unsigned    not null,
    user_id    bigint unsigned not null,
    created_at timestamp       null,
    updated_at timestamp       null,
    deleted_at timestamp       null,
    constraint role_user_role_id_foreign
        foreign key (role_id) references quantimodo_test.roles (id)
            on delete cascade,
    constraint role_user_user_id_foreign
        foreign key (user_id) references quantimodo_test.wp_users (ID)
            on delete cascade
)
    collate = utf8mb3_unicode_ci;

create index role_user_role_id_index
    on quantimodo_test.role_user (role_id);

create index role_user_user_id_index
    on quantimodo_test.role_user (user_id);

