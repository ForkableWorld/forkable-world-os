create table quantimodo_test.permission_user
(
    id            int unsigned auto_increment
        primary key,
    permission_id int unsigned    not null,
    user_id       bigint unsigned not null,
    created_at    timestamp       null,
    updated_at    timestamp       null,
    deleted_at    timestamp       null,
    constraint permission_user_permission_id_foreign
        foreign key (permission_id) references quantimodo_test.permissions (id)
            on delete cascade,
    constraint permission_user_user_id_foreign
        foreign key (user_id) references quantimodo_test.wp_users (ID)
            on delete cascade
)
    collate = utf8mb3_unicode_ci;

create index permission_user_permission_id_index
    on quantimodo_test.permission_user (permission_id);

create index permission_user_user_id_index
    on quantimodo_test.permission_user (user_id);

