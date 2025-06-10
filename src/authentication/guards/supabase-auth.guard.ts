import { Injectable, CanActivate, ExecutionContext } from '@nestjs/common';
import { SupabaseService } from '../../common/supabase/supabase.service';

@Injectable()
export class SupabaseAuthGuard implements CanActivate {
  constructor(private readonly supabaseService: SupabaseService) {}

  async canActivate(context: ExecutionContext): Promise<boolean> {
    const request = context.switchToHttp().getRequest();
    const authHeader = request.headers['authorization'];
    if (!authHeader) return false;

    const token = authHeader.split(' ')[1];
    const supabase = this.supabaseService.getSupabaseClient();

    try {
      const { data, error } = await supabase.auth.getUser(token);
      if (error || !data.user) return false;
      request.user = data.user;
      return true;
    } catch (error) {
      return false;
    }
  }
}