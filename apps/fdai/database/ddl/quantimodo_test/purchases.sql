create table quantimodo_test.purchases
(
    id                                   bigint unsigned auto_increment
        primary key,
    subscriber_user_id                   bigint unsigned                     not null,
    referrer_user_id                     bigint unsigned                     null,
    updated_at                           timestamp default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP,
    created_at                           timestamp default CURRENT_TIMESTAMP not null,
    subscription_provider                enum ('stripe', 'apple', 'google')  not null,
    last_four                            varchar(4)                          null,
    product_id                           varchar(100)                        not null,
    subscription_provider_transaction_id varchar(100)                        null,
    coupon                               varchar(100)                        null,
    client_id                            varchar(80)                         null,
    refunded_at                          date                                null,
    deleted_at                           timestamp                           null,
    constraint subscriber_referrer
        unique (subscriber_user_id, referrer_user_id),
    constraint purchases_client_id_fk
        foreign key (client_id) references quantimodo_test.oa_clients (client_id),
    constraint purchases_wp_users_ID_fk
        foreign key (subscriber_user_id) references quantimodo_test.wp_users (ID)
)
    charset = utf8mb3;

