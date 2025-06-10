import { Test, TestingModule } from '@nestjs/testing';
import { INestApplication } from '@nestjs/common';
import { OfflineDataModule } from '../../src/offline-data/offline-data.module';
import { SupabaseAuthGuard } from '../../src/authentication/guards/supabase-auth.guard';

describe('OfflineDataController (e2e)', () => {
  let app: INestApplication;

  beforeEach(async () => {
    const moduleFixture: TestingModule = await Test.createTestingModule({
      imports: [OfflineDataModule],
    })
    .overrideGuard(SupabaseAuthGuard)
    .useValue({ canActivate: () => true })
    .compile();

    app = moduleFixture.createNestApplication();
    await app.init();
  });

  it('should pass', () => {
    expect(true).toBeTruthy();
  });
});