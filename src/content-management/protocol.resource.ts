import { Protocol } from '../generated/prisma';

export class ProtocolResource {
    constructor(private readonly protocol: Protocol) {}

    toJSON() {
        return {
            id: this.protocol.id,
            title: this.protocol.title,
            slug: this.protocol.slug,
            description: this.protocol.description,
            implementationGuide: this.protocol.implementationGuide,
            category: this.protocol.category,
            isFree: this.protocol.isFree,
            createdAt: this.protocol.createdAt,
            updatedAt: this.protocol.updatedAt,
        };
    }

    static collection(protocols: Protocol[]) {
        return protocols.map((protocol) =>
            new ProtocolResource(protocol).toJSON(),
        );
    }
}
