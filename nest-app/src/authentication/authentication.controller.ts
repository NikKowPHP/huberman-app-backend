import { Controller, Post, Body, Req, Res } from '@nestjs/common';
import { AuthenticationService } from './authentication.service';

@Controller('authentication')
export class AuthenticationController {
  constructor(private readonly authenticationService: AuthenticationService) {}

  @Post('register')
  async register(@Body('email') email: string, @Body('password') password: string) {
    return this.authenticationService.register(email, password);
  }

  @Post('login')
  async login(@Body('email') email: string, @Body('password') password: string) {
    return this.authenticationService.login(email, password);
  }

  @Post('logout')
  async logout(@Req() req: Request, @Res({ passthrough: true }) res: Response) {
    await this.authenticationService.logout();
    return { message: 'Logged out successfully' };
  }
}