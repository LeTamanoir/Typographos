declare namespace Typographos {
    export namespace Tests {
        export namespace Fixtures {
            export interface WithInlineEnums {
                inlineStringEnum: "pending" | "active" | "inactive";
                inlineIntEnum: 1 | 2 | 3 | 4;
                regularStringEnum: Typographos.Tests.Fixtures.StringEnum
                regularIntEnum: Typographos.Tests.Fixtures.IntEnum
            }
            export type StringEnum = "pending" | "active" | "inactive";
            export type IntEnum = 1 | 2 | 3 | 4;
        }
    }
}
