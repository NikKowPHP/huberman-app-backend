import { Injectable } from '@nestjs/common';
import { PrismaService } from '../common/prisma/prisma.service';

@Injectable()
export class PostService {
  constructor(private readonly prisma: PrismaService) {}

  async createPost(userId: string, data: { title: string; content: string }) {
    return this.prisma.post.create({
      data: {
        userId,
        title: data.title,
        content: data.content,
        status: 'published'
      }
    });
  }

  async getPostsWithComments() {
    return this.prisma.post.findMany({
      include: {
        user: true,
        comments: {
          include: {
            user: true
          }
        }
      },
      orderBy: {
        createdAt: 'desc'
      },
      take: 10
    });
  }

  async createComment(userId: string, postId: string, data: { content: string }) {
    return this.prisma.comment.create({
      data: {
        userId,
        postId,
        content: data.content
      }
    });
  }
}