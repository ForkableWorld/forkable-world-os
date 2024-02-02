create table quantimodo_test.child_parents
(
    id             int unsigned auto_increment
        primary key,
    child_user_id  bigint unsigned                     not null comment 'The child who has granted data access to the parent. ',
    parent_user_id bigint unsigned                     not null comment 'The parent who has been granted access to the child data.',
    scopes         varchar(2000)                       not null comment 'Whether the parent has read access and/or write access to the data.',
    created_at     timestamp default CURRENT_TIMESTAMP not null,
    updated_at     timestamp default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP,
    deleted_at     timestamp                           null,
    constraint child_user_id_parent_user_id_uindex
        unique (child_user_id, parent_user_id),
    constraint child_parents_wp_users_ID_fk
        foreign key (child_user_id) references quantimodo_test.wp_users (ID),
    constraint child_parents_wp_users_ID_fk_2
        foreign key (parent_user_id) references quantimodo_test.wp_users (ID)
)
    charset = latin1;

