import { Injectable } from '@nestjs/common';
import { PrismaService } from '../common/prisma/prisma.service';

@Injectable()
export class PostService {
  constructor(private readonly prisma: PrismaService) {}

  async createPost(userId: string, data: any) {
    throw new Error('Method not implemented');
  }

  async getPostsWithComments() {
    throw new Error('Method not implemented');
  }

  async createComment(userId: string, postId: string, data: any) {
    throw new Error('Method not implemented');
  }
}