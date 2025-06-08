import { PrismaClient } from '@prisma/client';

const prisma = new PrismaClient();

async function main() {
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
}

main()
  .catch((e) => {
    console.error(e);
    process.exit(1);
  })
  .finally(async () => {
    await prisma.$disconnect();
  });