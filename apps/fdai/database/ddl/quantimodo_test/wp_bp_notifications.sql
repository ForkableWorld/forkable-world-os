create table quantimodo_test.wp_bp_notifications
(
    id                bigint auto_increment
        primary key,
    user_id           bigint unsigned                      not null,
    item_id           bigint                               not null,
    secondary_item_id bigint                               null,
    component_name    varchar(75)                          not null,
    component_action  varchar(75)                          not null,
    date_notified     datetime                             not null,
    is_new            tinyint(1) default 0                 not null,
    updated_at        timestamp  default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP,
    created_at        timestamp  default CURRENT_TIMESTAMP not null,
    deleted_at        timestamp                            null,
    client_id         varchar(255)                         null,
    constraint wp_bp_notifications_wp_users_ID_fk
        foreign key (user_id) references quantimodo_test.wp_users (ID)
)
    charset = utf8mb3;

create index component_action
    on quantimodo_test.wp_bp_notifications (component_action);

create index component_name
    on quantimodo_test.wp_bp_notifications (component_name);

create index is_new
    on quantimodo_test.wp_bp_notifications (is_new);

create index item_id
    on quantimodo_test.wp_bp_notifications (item_id);

create index secondary_item_id
    on quantimodo_test.wp_bp_notifications (secondary_item_id);

create index user_id
    on quantimodo_test.wp_bp_notifications (user_id);

create index useritem
    on quantimodo_test.wp_bp_notifications (user_id, is_new);

