import { Injectable } from '@nestjs/common';
import { HttpService } from '@nestjs/axios';
import * as jose from 'jose';
import { firstValueFrom } from 'rxjs';

interface AppleKey {
  kty: string;
  kid: string;
  use: string;
  alg: string;
  n?: string;
  e?: string;
  crv?: string;
  x?: string;
  y?: string;
}

interface AppleKeysResponse {
  keys: AppleKey[];
}

@Injectable()
export class AppleService {
  private applePublicKeysUrl = 'https://appleid.apple.com/auth/keys';
  
  constructor(private httpService: HttpService) {}

  async decodeAndVerifyJWS(jws: string): Promise<any> {
    // Step 1: Get header to extract kid
    const header = jose.decodeProtectedHeader(jws);
    if (!header.kid) {
      throw new Error('Missing kid in JWS header');
    }

    // Step 2: Fetch Apple's public keys
    const response = await firstValueFrom(
      this.httpService.get<AppleKeysResponse>(this.applePublicKeysUrl)
    );
    const keys = response.data.keys;
    
    // Step 3: Find matching key
    const matchingKey = keys.find((key) => key.kid === header.kid);
    if (!matchingKey) {
      throw new Error(`No matching public key found for kid: ${header.kid}`);
    }

    // Step 4: Import key
    const publicKey = await jose.importJWK(matchingKey, matchingKey.alg);

    // Step 5: Verify JWS (only need payload)
    const { payload } = await jose.jwtVerify(jws, publicKey, {
      algorithms: ['ES256'],
    });

    return payload;
  }

  async handleDidChangeRenewalStatus(jws: string): Promise<void> {
    const payload = await this.decodeAndVerifyJWS(jws);
    const autoRenewStatus = payload.data?.autoRenewStatus;
    const productId = payload.data?.productId;

    if (autoRenewStatus === false && productId) {
      // In a real implementation, we would update the subscription status
      // This would interact with the Prisma service to update the database
      console.log(`Subscription for product ${productId} canceled`);
    }
  }
}