create table quantimodo_test.schedule_histories
(
    id          int unsigned auto_increment
        primary key,
    schedule_id int unsigned not null,
    command     varchar(255) not null,
    params      text         null,
    output      text         not null,
    options     text         null,
    created_at  timestamp    null,
    updated_at  timestamp    null,
    constraint schedule_histories_schedule_id_foreign
        foreign key (schedule_id) references quantimodo_test.schedules (id)
)
    collate = utf8mb3_unicode_ci;

