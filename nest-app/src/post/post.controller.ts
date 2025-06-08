import { Controller, Get, Post, Body, Req, UseGuards, Param } from '@nestjs/common';
import { SupabaseAuthGuard } from '../authentication/guards/supabase-auth.guard';
import { PostService } from './post.service';

@Controller('posts')
@UseGuards(SupabaseAuthGuard)
export class PostController {
  constructor(private readonly postService: PostService) {}

  @Get()
  async getPostsWithComments() {
    throw new Error('Method not implemented');
  }

  @Post()
  async createPost(@Req() req, @Body() data: any) {
    throw new Error('Method not implemented');
  }

  @Post(':id/comments')
  async createComment(@Req() req, @Param('id') postId: string, @Body() data: any) {
    throw new Error('Method not implemented');
  }
}