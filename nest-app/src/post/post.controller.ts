import { Controller, Get, Post, Body, Req, UseGuards, Param } from '@nestjs/common';
import { SupabaseAuthGuard } from '../authentication/guards/supabase-auth.guard';
import { PostService } from './post.service';
import { CreatePostDto } from './dto/create-post.dto';
import { StoreCommentDto } from './dto/store-comment.dto';

@Controller('posts')
@UseGuards(SupabaseAuthGuard)
export class PostController {
  constructor(private readonly postService: PostService) {}

  @Get()
  async getPostsWithComments() {
    return this.postService.getPostsWithComments();
  }

  @Post()
  async createPost(@Req() req, @Body() createPostDto: CreatePostDto) {
    const userId = req.user.sub;
    return this.postService.createPost(userId, createPostDto);
  }

  @Post(':id/comments')
  async createComment(
    @Req() req,
    @Param('id') postId: string,
    @Body() storeCommentDto: StoreCommentDto
  ) {
    const userId = req.user.sub;
    return this.postService.createComment(userId, postId, storeCommentDto);
  }
}