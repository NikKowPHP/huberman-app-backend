import { Injectable } from '@nestjs/common';
import { SupabaseService } from '../common/supabase/supabase.service';

@Injectable()
export class AuthenticationService {
  constructor(private readonly supabaseService: SupabaseService) {}

  async register(email: string, password: string) {
    const supabase = this.supabaseService.getSupabaseClient();
    const { data, error } = await supabase.auth.signUp({ email, password });
    if (error) throw error;
    return data;
  }

  async login(email: string, password: string) {
    const supabase = this.supabaseService.getSupabaseClient();
    const { data, error } = await supabase.auth.signInWithPassword({ email, password });
    if (error) throw error;
    return data;
  }

  async logout() {
    const supabase = this.supabaseService.getSupabaseClient();
    const { error } = await supabase.auth.signOut();
    if (error) throw error;
    return { message: 'Logged out successfully' };
  }
async resetPassword(email: string) {
    const supabase = this.supabaseService.getSupabaseClient();
    const { error } = await supabase.auth.resetPasswordForEmail(email, {
      redirectTo: process.env.PASSWORD_RESET_URL, // You'll need to add this to your .env
    });
    if (error) throw error;
    return { message: 'Password reset email sent successfully. Please check your inbox.' };
  }
}