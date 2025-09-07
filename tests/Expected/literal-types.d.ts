declare namespace Typographos {
    export namespace Tests {
        export namespace Fixtures {
            export interface LiteralTypes {
                literalNumber: 42
                literalString: "hello"
                literalBoolean: true
                enumReference: MyEnum.VALUE
                templateLiteral: `template-${string}`
                literalNull: null
                regularProperty: string
            }
        }
    }
}
