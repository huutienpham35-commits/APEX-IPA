#import <Foundation/Foundation.h>
#import <UIKit/UIKit.h>

NS_ASSUME_NONNULL_BEGIN

// Public customer header — keep tiny. Secrets/endpoints live sealed inside libAPIClient.a.
#define TSERVER_SDK_RELEASE_VERSION @"2.0.8"
#define APICLIENT_HAS_TERMINAL_EVENTS 1

// Token của app được giải mã lúc chạy từ ProtectedConfiguration.swift.
// Giữ hằng public này rỗng để token pkg_ không xuất hiện dạng plaintext trong binary.
static NSString * const kAPIClientPackageToken = @"";

FOUNDATION_EXTERN void APIClientConfigure(NSString * _Nullable packageToken);
FOUNDATION_EXTERN void APIClientStartAuthorization(dispatch_block_t _Nullable onAuthorized,
                                                    dispatch_block_t _Nullable onRevoked);
typedef void (^APIClientTerminalEventBlock)(NSDictionary *result);
FOUNDATION_EXTERN void APIClientStartAuthorizationWithEvents(
    dispatch_block_t _Nullable onAuthorized,
    dispatch_block_t _Nullable onRevoked,
    APIClientTerminalEventBlock _Nullable onTerminal
);
FOUNDATION_EXTERN BOOL APIClientPerformAuthorized(NSString * _Nullable capability,
                                                   dispatch_block_t _Nullable work,
                                                   dispatch_block_t _Nullable denied);
FOUNDATION_EXTERN BOOL APIClientHandleOpenURL(NSURL * _Nullable url);
FOUNDATION_EXTERN NSString *APIClientRenderTemplate(NSString * _Nullable templateText);

NS_INLINE void APIClientSetup(void) {
    NSString *token = [kAPIClientPackageToken stringByTrimmingCharactersInSet:NSCharacterSet.whitespaceAndNewlineCharacterSet];
    if (token.length >= 24 && [token rangeOfString:@"REPLACE" options:NSCaseInsensitiveSearch].location == NSNotFound) {
        APIClientConfigure(token);
    }
}

#ifndef APICLIENT_NO_AUTO_SETUP
__attribute__((constructor)) static void APIClientHeaderAutoSetup(void) {
    APIClientSetup();
}
#endif

@interface APIClient : NSObject
+ (void)startAuthorization:(dispatch_block_t _Nullable)onAuthorized
                 onRevoked:(dispatch_block_t _Nullable)onRevoked;
+ (void)startAuthorization:(dispatch_block_t _Nullable)onAuthorized
                 onRevoked:(dispatch_block_t _Nullable)onRevoked
                onTerminal:(APIClientTerminalEventBlock _Nullable)onTerminal;
+ (BOOL)performAuthorized:(NSString * _Nullable)capability
                     work:(dispatch_block_t _Nullable)work
                   denied:(dispatch_block_t _Nullable)denied;
+ (NSString *)renderTemplate:(NSString * _Nullable)templateText;
+ (NSDictionary * _Nullable)currentKeyInfo;
+ (NSString *)currentKeyText;
+ (NSString *)currentKeyRemainingText;
+ (NSInteger)currentKeyMaxDevices;

// Compatibility shims (still linked in .a; not advertised).
+ (void)start:(dispatch_block_t _Nullable)onPaid
    __attribute__((deprecated("Use startAuthorization:onRevoked:")));
+ (BOOL)isValid
    __attribute__((deprecated("Use performAuthorized:work:denied:")));
+ (void)paid:(dispatch_block_t _Nullable)onPaid
    __attribute__((deprecated("Use startAuthorization:onRevoked:")));
@end

// Keep C shims available for older Logos sources.
FOUNDATION_EXTERN void APIClientStart(dispatch_block_t _Nullable onPaid)
    __attribute__((deprecated("Use APIClientStartAuthorization")));
FOUNDATION_EXTERN BOOL APIClientIsValid(void)
    __attribute__((deprecated("Use APIClientPerformAuthorized")));

NS_ASSUME_NONNULL_END
