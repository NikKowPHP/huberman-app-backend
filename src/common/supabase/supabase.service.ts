import { Injectable } from '@nestjs/common';
import { createClient, SupabaseClient } from '@supabase/supabase-js';

@Injectable()
export class SupabaseService {
  private supabaseUrl: string;
  private supabaseKey: string;
  private supabase: SupabaseClient;

  constructor() {
    this.supabaseUrl = process.env.SUPABASE_URL;
    this.supabaseKey = process.env.SUPABASE_KEY;
    this.supabase = createClient(this.supabaseUrl, this.supabaseKey);
  }

  getSupabaseClient(): SupabaseClient {
    return this.supabase;
  }

  // Add methods for Supabase interactions here
}