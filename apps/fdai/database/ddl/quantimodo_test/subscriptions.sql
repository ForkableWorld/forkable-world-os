create table quantimodo_test.subscriptions
(
    id            bigint unsigned auto_increment
        primary key,
    user_id       bigint unsigned not null,
    name          varchar(255)    not null,
    stripe_id     varchar(255)    not null,
    stripe_status varchar(255)    not null,
    stripe_plan   varchar(255)    null,
    quantity      int             null,
    trial_ends_at timestamp       null,
    ends_at       timestamp       null,
    created_at    timestamp       null,
    updated_at    timestamp       null
)
    collate = utf8mb4_unicode_ci;

create index subscriptions_user_id_stripe_status_index
    on quantimodo_test.subscriptions (user_id, stripe_status);

