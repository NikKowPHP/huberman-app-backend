import { Controller, Get, Param, UseGuards } from '@nestjs/common';
import { ContentService } from './content.service';
import { SupabaseAuthGuard } from '../common/guards/supabase-auth.guard';
import { PremiumGuard } from '../common/guards/premium.guard';

@Controller('protocols')
export class ProtocolController {
  constructor(private readonly contentService: ContentService) {}

  @Get()
  @UseGuards(SupabaseAuthGuard, PremiumGuard)
  async index() {
    const protocols = await this.contentService.getProtocols();
    return protocols;
  }

  @Get(':id')
  @UseGuards(SupabaseAuthGuard)
  async show(@Param('id') id: number) {
    const protocol = await this.contentService.getProtocolDetails(id);
    return protocol;
  }
}