declare namespace Typographos {
    export namespace Tests {
        export namespace Fixtures {
            export interface WithEnums {
                regularStatus: Typographos.Tests.Fixtures.StringEnum
                regularPriority: Typographos.Tests.Fixtures.IntEnum
            }
            export enum StringEnum {
                PENDING = "pending",
                ACTIVE = "active",
                INACTIVE = "inactive",
            }
            export enum IntEnum {
                LOW = 1,
                MEDIUM = 2,
                HIGH = 3,
                URGENT = 4,
            }
        }
    }
}
