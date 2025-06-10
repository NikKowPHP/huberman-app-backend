import { PrismaClient } from '@prisma/client';
import { faker } from '@faker-js/faker';

const prisma = new PrismaClient();

interface UserSeed {
    id: string;
}

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
    await prisma.episode.createMany({
        data: Array.from({ length: 5 }).map(() => ({
            title: faker.lorem.words(3),
            slug: faker.lorem.slug(3),
            description: faker.lorem.sentence(),
            content: faker.lorem.paragraph(),
            duration: faker.number.int({ min: 10, max: 60 }),
            publishedAt: faker.date.past(),
        })),
    });

    // Seed protocols and get their IDs
    const protocolData = [
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
    ];
    
    const protocols = [];
    for (const data of protocolData) {
        const protocol = await prisma.protocol.create({ data });
        protocols.push(protocol);
    }

    // Seed episode-protocol relationships
    for (const episode of await prisma.episode.findMany()) {
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
    for (const episode of await prisma.episode.findMany()) {
        await prisma.summary.createMany({
            data: Array.from({ length: 2 }).map(() => ({
                episodeId: episode.id,
                content: faker.lorem.paragraph(),
            })),
        });
    }

    // Seed note tags
    await prisma.noteTag.createMany({
        data: Array.from({ length: 10 }).map(() => ({
            name: faker.lorem.word(),
            color: faker.color.rgb(),
        })),
    });

    // Get all users with proper typing
    const users: UserSeed[] = await prisma.user.findMany();

    // Seed user reminders
    for (const user of users) {
        await prisma.userReminder.createMany({
            data: Array.from({ length: 3 }).map(() => ({
                userId: user.id,
                protocolId: faker.helpers.arrayElement(protocols).id,
                reminderTime: faker.date.anytime().toISOString(),
                frequency: faker.helpers.arrayElement([
                    'daily',
                    'weekly',
                    'specific_days',
                ]),
                specificDays:
                    faker.helpers.arrayElement(['daily', 'weekly', 'specific_days']) ===
                    'specific_days'
                        ? faker.helpers.arrayElements(
                              ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                              faker.number.int({ min: 1, max: 7 }),
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
                startTime: faker.date.anytime(),
                endTime: faker.date.anytime(),
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
                duration: faker.number.int({ min: 1, max: 30 }),
                order: faker.number.int({ min: 1, max: 10 }),
                isOptional: faker.datatype.boolean({ probability: 0.3 }),
            })),
        });
    }

    // Seed posts
    const posts = [];
    for (const user of users) {
        await prisma.post.createMany({
            data: Array.from({ length: 3 }).map(() => ({
                userId: user.id,
                title: faker.lorem.words(5),
                content: faker.lorem.paragraphs(3),
            })),
        });
        const userPosts = await prisma.post.findMany({
            where: { userId: user.id },
            take: 3,
        });
        posts.push(...userPosts);
    }

    // Seed comments
    for (const post of posts) {
        await prisma.comment.createMany({
            data: Array.from({ length: 5 }).map(() => ({
                postId: post.id,
                userId: faker.helpers.arrayElement(users).id,
                content: faker.lorem.paragraph(),
            })),
        });
    }
}

main()
    .then(async () => {
        await prisma.$disconnect();
    })
    .catch(async (e) => {
        console.error(e);
        await prisma.$disconnect();
        process.exit(1);
    });
