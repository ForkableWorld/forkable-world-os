create table quantimodo_test.unit_categories
(
    id            tinyint unsigned auto_increment
        primary key,
    name          varchar(64)                          not null comment 'Unit category name',
    created_at    timestamp  default CURRENT_TIMESTAMP not null,
    updated_at    timestamp  default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP,
    can_be_summed tinyint(1) default 1                 not null,
    deleted_at    timestamp                            null,
    sort_order    int                                  not null,
    constraint name_UNIQUE
        unique (name)
)
    comment 'Category for the unit of measurement such as weight, rating, distance, or volume.' charset = utf8mb3;

