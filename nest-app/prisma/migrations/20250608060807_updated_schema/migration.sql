/*
  Warnings:

  - A unique constraint covering the columns `[stripeId]` on the table `Subscription` will be added. If there are existing duplicate values, this will fail.
  - Added the required column `name` to the `Subscription` table without a default value. This is not possible if the table is not empty.
  - Added the required column `quantity` to the `Subscription` table without a default value. This is not possible if the table is not empty.
  - Added the required column `stripeId` to the `Subscription` table without a default value. This is not possible if the table is not empty.
  - Added the required column `stripePrice` to the `Subscription` table without a default value. This is not possible if the table is not empty.
  - Added the required column `stripeStatus` to the `Subscription` table without a default value. This is not possible if the table is not empty.
  - Added the required column `updatedAt` to the `Subscription` table without a default value. This is not possible if the table is not empty.

*/
-- AlterTable
ALTER TABLE "Subscription" ADD COLUMN     "createdAt" TIMESTAMP(3) NOT NULL DEFAULT CURRENT_TIMESTAMP,
ADD COLUMN     "endsAt" TIMESTAMP(3),
ADD COLUMN     "name" TEXT NOT NULL,
ADD COLUMN     "quantity" INTEGER NOT NULL,
ADD COLUMN     "stripeId" TEXT NOT NULL,
ADD COLUMN     "stripePrice" TEXT NOT NULL,
ADD COLUMN     "stripeStatus" TEXT NOT NULL,
ADD COLUMN     "trialEndsAt" TIMESTAMP(3),
ADD COLUMN     "updatedAt" TIMESTAMP(3) NOT NULL;

-- CreateTable
CREATE TABLE "Episode" (
    "id" TEXT NOT NULL,
    "title" TEXT NOT NULL,
    "slug" TEXT NOT NULL,
    "description" TEXT,
    "content" TEXT,
    "duration" INTEGER,
    "publishedAt" TIMESTAMP(3),
    "createdAt" TIMESTAMP(3) NOT NULL DEFAULT CURRENT_TIMESTAMP,
    "updatedAt" TIMESTAMP(3) NOT NULL,

    CONSTRAINT "Episode_pkey" PRIMARY KEY ("id")
);

-- CreateTable
CREATE TABLE "Protocol" (
    "id" TEXT NOT NULL,

    CONSTRAINT "Protocol_pkey" PRIMARY KEY ("id")
);

-- CreateTable
CREATE TABLE "EpisodeProtocol" (
    "episodeId" TEXT NOT NULL,
    "protocolId" TEXT NOT NULL,

    CONSTRAINT "EpisodeProtocol_pkey" PRIMARY KEY ("episodeId","protocolId")
);

-- CreateIndex
CREATE UNIQUE INDEX "Episode_slug_key" ON "Episode"("slug");

-- CreateIndex
CREATE UNIQUE INDEX "Subscription_stripeId_key" ON "Subscription"("stripeId");

-- AddForeignKey
ALTER TABLE "EpisodeProtocol" ADD CONSTRAINT "EpisodeProtocol_episodeId_fkey" FOREIGN KEY ("episodeId") REFERENCES "Episode"("id") ON DELETE RESTRICT ON UPDATE CASCADE;

-- AddForeignKey
ALTER TABLE "EpisodeProtocol" ADD CONSTRAINT "EpisodeProtocol_protocolId_fkey" FOREIGN KEY ("protocolId") REFERENCES "Protocol"("id") ON DELETE RESTRICT ON UPDATE CASCADE;
