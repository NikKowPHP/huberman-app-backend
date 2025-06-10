/*
  Warnings:

  - A unique constraint covering the columns `[googlePlaySubscriptionId]` on the table `Subscription` will be added. If there are existing duplicate values, this will fail.
  - A unique constraint covering the columns `[appleOriginalTransactionId]` on the table `User` will be added. If there are existing duplicate values, this will fail.
  - A unique constraint covering the columns `[googlePlayPurchaseToken]` on the table `User` will be added. If there are existing duplicate values, this will fail.

*/
-- AlterTable
ALTER TABLE "Subscription" ADD COLUMN     "appleOriginalTransactionId" TEXT,
ADD COLUMN     "googlePlaySubscriptionId" TEXT;

-- AlterTable
ALTER TABLE "User" ADD COLUMN     "appleOriginalTransactionId" TEXT,
ADD COLUMN     "googlePlayPurchaseToken" TEXT;

-- CreateIndex
CREATE UNIQUE INDEX "Subscription_googlePlaySubscriptionId_key" ON "Subscription"("googlePlaySubscriptionId");

-- CreateIndex
CREATE UNIQUE INDEX "User_appleOriginalTransactionId_key" ON "User"("appleOriginalTransactionId");

-- CreateIndex
CREATE UNIQUE INDEX "User_googlePlayPurchaseToken_key" ON "User"("googlePlayPurchaseToken");
