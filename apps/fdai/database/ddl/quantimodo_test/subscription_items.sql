create table quantimodo_test.subscription_items
(
    id              bigint unsigned auto_increment
        primary key,
    subscription_id bigint unsigned not null,
    stripe_id       varchar(255)    not null,
    stripe_plan     varchar(255)    not null,
    quantity        int             null,
    created_at      timestamp       null,
    updated_at      timestamp       null,
    constraint subscription_items_subscription_id_stripe_plan_unique
        unique (subscription_id, stripe_plan)
)
    collate = utf8mb4_unicode_ci;

create index subscription_items_stripe_id_index
    on quantimodo_test.subscription_items (stripe_id);

