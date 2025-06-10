import { Test, TestingModule } from '@nestjs/testing';
import { INestApplication } from '@nestjs/common';
import { RoutineModule } from '../../src/routine/routine.module';
import { SupabaseAuthGuard } from '../../src/authentication/guards/supabase-auth.guard';

describe('RoutineController (e2e)', () => {
  let app: INestApplication;

  beforeEach(async () => {
    const moduleFixture: TestingModule = await Test.createTestingModule({
      imports: [RoutineModule],
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