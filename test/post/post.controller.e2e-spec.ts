import { Test, TestingModule } from '@nestjs/testing';
import { INestApplication } from '@nestjs/common';
import { PostModule } from '../../src/post/post.module';
import { SupabaseAuthGuard } from '../../src/authentication/guards/supabase-auth.guard';
import { PrismaService } from '../../src/common/prisma/prisma.service';

describe('PostController (e2e)', () => {
  let app: INestApplication;

  beforeEach(async () => {
    const moduleFixture: TestingModule = await Test.createTestingModule({
      imports: [PostModule],
      providers: [PrismaService],
    })
    .overrideGuard(SupabaseAuthGuard)
    .useValue({ canActivate: () => true })
    .compile();

    app = moduleFixture.createNestApplication();
    await app.init();
  });

  afterAll(async () => {
    await app.close();
  });

  it('should pass', () => {
    expect(true).toBeTruthy();
  });
});