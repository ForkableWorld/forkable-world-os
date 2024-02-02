import { PrismaClient } from "@prisma/client";
const prisma = new PrismaClient();

async function main() {
    const connectors = await prisma.connectors.findMany();
    const loginConnectors = connectors.filter(connector =>
        connector.name === "googleplus" ||
        connector.name === "facebook" ||
        connector.name === "twitter" ||
        connector.name === "github");
    const connectorIds = loginConnectors.map(connector => connector.id);
    // ... you will write your Prisma Client queries here
    let connections = await prisma.connections.findMany({
        where: {
            connector_user_id: {
                equals: null
            },
            connector_id: {
                in: connectorIds
            }
        },
        include: {
            connector: true,
            human: true
        }
    });
    for (const connectionsKey in connections) {
        const connection = connections[connectionsKey];
        const connector = connection.connector;
        const connectorName = connector.name;
        console.log(`Connector name: ${connectorName} - ${connection.human.email}`);
        var meta = await prisma.wp_usermeta.findMany({
            where: {
                user_id: {
                    equals: connection.user_id
                },
                meta_key: {
                    contains: "%" + connectorName + "%"
                }
            }
        });
        for(const metaKey in meta) {
            var metaItem = meta[metaKey];
            if(metaItem.meta_key === connectorName + "_connector_user_id") {
                let connector_user_id = metaItem.meta_value;
                let res = await prisma.connections.update({
                    where: {
                        id: connection.id
                    },
                    data: {
                        connector_user_id
                    }
                });
                console.log(`Updated connection ${connection.id} with connector_user_id ${metaItem.meta_value}. Result: ${res}`);
            }

        }
    }
}

main()
    .then(async () => {
        await prisma.$disconnect()
    })
    .catch(async (e) => {
        console.error(e)
        await prisma.$disconnect()
        process.exit(1)
    })
