create table quantimodo_test.permission_role
(
    id            int unsigned auto_increment
        primary key,
    permission_id int unsigned not null,
    role_id       int unsigned not null,
    created_at    timestamp    null,
    updated_at    timestamp    null,
    deleted_at    timestamp    null,
    constraint permission_role_permission_id_foreign
        foreign key (permission_id) references quantimodo_test.permissions (id)
            on delete cascade,
    constraint permission_role_role_id_foreign
        foreign key (role_id) references quantimodo_test.roles (id)
            on delete cascade
)
    collate = utf8mb3_unicode_ci;

create index permission_role_permission_id_index
    on quantimodo_test.permission_role (permission_id);

create index permission_role_role_id_index
    on quantimodo_test.permission_role (role_id);

