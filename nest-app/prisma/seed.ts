import { PrismaClient } from '@prisma/client';
import { faker } from '@faker-js/faker';

const prisma = new PrismaClient();

async function main() {
    // Seed plans
    await prisma.plan.createMany({
        data: [
            {
                name: 'Free',
                slug: 'free',
                description: 'Free plan',
                price: 0,
                interval: 'MONTH',
                intervalCount: 1,
                trialPeriodDays: 0,
                isActive: true,
            },
            {
                name: 'Premium Monthly',
                slug: 'premium-monthly',
                description: 'Premium monthly plan',
                price: 10,
                interval: 'MONTH',
                intervalCount: 1,
                trialPeriodDays: 7,
                isActive: true,
            },
            {
                name: 'Premium Annual',
                slug: 'premium-annual',
                description: 'Premium annual plan',
                price: 100,
                interval: 'YEAR',
                intervalCount: 1,
                trialPeriodDays: 30,
                isActive: true,
            },
        ],
    });

    // Seed episodes
    const episodes = await prisma.episode.createMany({
        data: Array.from({ length: 5 }).map(() => ({
            title: faker.lorem.words(3),
            slug: faker.lorem.slug(3),
            description: faker.lorem.sentence(),
            content: faker.lorem.paragraph(),
            duration: faker.datatype.number({ min: 10, max: 60 }),
            publishedAt: faker.date.past(),
        })),
    });

    // Seed protocols
    const protocols = await prisma.protocol.createMany({
        data: [
            ...Array.from({ length: 5 }).map(() => ({
                title: faker.lorem.words(3),
                slug: faker.lorem.slug(3),
                description: faker.lorem.sentence(),
                implementationGuide: faker.lorem.paragraph(),
                isFree: true,
            })),
            ...Array.from({ length: 2 }).map(() => ({
                title: faker.lorem.words(3),
                slug: faker.lorem.slug(3),
                description: faker.lorem.sentence(),
                implementationGuide: faker.lorem.paragraph(),
                isFree: false,
            })),
        ],
    });

    // Seed episode-protocol relationships
    for (const episode of episodes) {
        for (const protocol of protocols) {
            if (Math.random() > 0.5) {
                await prisma.episodeProtocol.create({
                    data: {
                        episodeId: episode.id,
                        protocolId: protocol.id,
                    },
                });
            }
        }
    }

    // Seed summaries
    for (const episode of episodes) {
        await prisma.summary.createMany({
            data: Array.from({ length: 2 }).map(() => ({
                episodeId: episode.id,
                content: faker.lorem.paragraph(),
            })),
        });
    }

    // Seed note tags
    const noteTags = await prisma.noteTag.createMany({
        data: Array.from({ length: 10 }).map(() => ({
            name: faker.lorem.word(),
            color: faker.color.rgb(),
        })),
    });

    // Seed user reminders
    const users = await prisma.user.findMany();
    for (const user of users) {
        await prisma.userReminder.createMany({
            data: Array.from({ length: 3 }).map(() => ({
                userId: user.id,
                protocolId: faker.helpers.arrayElement(protocols).id,
                reminderTime: faker.datatype.datetime().toISOString(),
                frequency: faker.helpers.arrayElement([
                    'daily',
                    'weekly',
                    'specific_days',
                ]),
                specificDays:
                    faker.helpers.arrayElement(['daily', 'weekly']) ===
                    'specific_days'
                        ? faker.helpers.arrayElements(
                              ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                              faker.datatype.number({ min: 1, max: 7 }),
                          )
                        : [],
                message: faker.lorem.sentence(),
                isActive: faker.datatype.boolean({ probability: 0.8 }),
            })),
        });
    }

    // Seed routines
    for (const user of users) {
        await prisma.routine.createMany({
            data: Array.from({ length: 2 }).map(() => ({
                userId: user.id,
                name: faker.lorem.words(3),
                description: faker.lorem.sentence(),
                frequency: faker.helpers.arrayElement([
                    'daily',
                    'weekly',
                    'weekdays',
                    'custom',
                ]),
                startTime: faker.datatype.datetime(),
                endTime: faker.datatype.datetime(),
                isActive: faker.datatype.boolean({ probability: 0.8 }),
            })),
        });
    }

    // Seed routine steps
    const routines = await prisma.routine.findMany();
    for (const routine of routines) {
        await prisma.routineStep.createMany({
            data: Array.from({ length: 3 }).map(() => ({
                routineId: routine.id,
                name: faker.lorem.words(3),
                description: faker.lorem.sentence(),
                duration: faker.datatype.number({ min: 1, max: 30 }),
                order: faker.datatype.uniqueNumber({ min: 1, max: 10 }),
                isOptional: faker.datatype.boolean({ probability: 0.2 }),
            })),
        });
    }
}

main()
    .catch((e) => {
        console.error(e);
        process.exit(1);
    })
    .finally(async () => {
        await prisma.$disconnect();
    });
